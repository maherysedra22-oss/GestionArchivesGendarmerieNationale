<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Liste des rôles.
     */
    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->withCount('utilisateurs')
            ->withCount('permissions')
            ->orderByDesc('systeme')
            ->orderBy('nom_role')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des rôles récupérée avec succès.',
            'data' => $roles,
        ]);
    }

    /**
     * Créer un rôle.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom_role' => [
                'required',
                'string',
                'max:50',
                'unique:roles,nom_role',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'actif' => [
                'sometimes',
                'boolean',
            ],
            'permissions' => [
                'sometimes',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id_permission',
            ],
        ]);

        $role = DB::transaction(function () use ($validated) {
            $role = Role::create([
                'nom_role' => $validated['nom_role'],
                'description' => $validated['description'] ?? null,
                'actif' => $validated['actif'] ?? true,
                'systeme' => false,
            ]);

            if (array_key_exists('permissions', $validated)) {
                $permissions = Permission::query()
                    ->where('actif', true)
                    ->whereIn('id_permission', $validated['permissions'])
                    ->pluck('id_permission')
                    ->toArray();

                $role->permissions()->sync($permissions);
            }

            return $role->load('permissions');
        });

        return response()->json([
            'success' => true,
            'message' => 'Rôle créé avec succès.',
            'data' => $role,
        ], 201);
    }

    /**
     * Afficher un rôle.
     */
    public function show(int $id): JsonResponse
    {
        $role = Role::query()
            ->with([
                'permissions' => function ($query) {
                    $query->where('actif', true)
                        ->orderBy('page')
                        ->orderBy('action');
                },
            ])
            ->withCount('utilisateurs')
            ->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rôle récupéré avec succès.',
            'data' => $role,
        ]);
    }

    /**
     * Modifier un rôle.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle introuvable.',
            ], 404);
        }

        // Un rôle système ne peut pas être modifié.
        if ($role->systeme) {
            return response()->json([
                'success' => false,
                'message' => 'Le rôle Administrateur est un rôle système et ne peut pas être modifié.',
            ], 403);
        }

        $validated = $request->validate([
            'nom_role' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'nom_role')
                    ->ignore($role->id_role, 'id_role'),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'actif' => [
                'sometimes',
                'boolean',
            ],
            'permissions' => [
                'sometimes',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id_permission',
            ],
        ]);

        $updatedRole = DB::transaction(function () use ($role, $validated) {
            $role->update([
                'nom_role' => $validated['nom_role'],
                'description' => $validated['description'] ?? null,
                'actif' => $validated['actif'] ?? $role->actif,
            ]);

            if (array_key_exists('permissions', $validated)) {
                $permissions = Permission::query()
                    ->where('actif', true)
                    ->whereIn('id_permission', $validated['permissions'])
                    ->pluck('id_permission')
                    ->toArray();

                $role->permissions()->sync($permissions);
            }

            return $role->load('permissions');
        });

        return response()->json([
            'success' => true,
            'message' => 'Rôle modifié avec succès.',
            'data' => $updatedRole,
        ]);
    }

    /**
     * Supprimer un rôle.
     */
    public function destroy(int $id): JsonResponse
    {
        $role = Role::withCount('utilisateurs')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle introuvable.',
            ], 404);
        }

        // Protection du rôle système.
        if ($role->systeme) {
            return response()->json([
                'success' => false,
                'message' => 'Le rôle Administrateur ne peut pas être supprimé.',
            ], 403);
        }

        // Impossible de supprimer un rôle utilisé par des utilisateurs.
        if ($role->utilisateurs_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle ne peut pas être supprimé car il est utilisé par des utilisateurs.',
                'utilisateurs' => $role->utilisateurs_count,
            ], 409);
        }

        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Rôle supprimé avec succès.',
        ]);
    }

    /**
     * Liste des permissions disponibles.
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::query()
            ->where('actif', true)
            ->orderBy('page')
            ->orderBy('action')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des permissions récupérée avec succès.',
            'data' => $permissions,
        ]);
    }

    /**
     * Liste des permissions d'un rôle.
     */
    public function rolePermissions(int $id): JsonResponse
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permissions du rôle récupérées avec succès.',
            'data' => [
                'role' => $role,
                'permissions' => $role->permissions,
            ],
        ]);
    }

    /**
     * Remplacer les permissions d'un rôle.
     */
    public function updatePermissions(Request $request, int $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle introuvable.',
            ], 404);
        }

        // Le rôle Administrateur possède toujours toutes les permissions.
        if ($role->systeme) {
            return response()->json([
                'success' => false,
                'message' => 'Les permissions du rôle Administrateur sont gérées automatiquement.',
            ], 403);
        }

        $validated = $request->validate([
            'permissions' => [
                'required',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id_permission',
            ],
        ]);

        $permissions = Permission::query()
            ->where('actif', true)
            ->whereIn('id_permission', $validated['permissions'])
            ->pluck('id_permission')
            ->toArray();

        $role->permissions()->sync($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions du rôle mises à jour avec succès.',
            'data' => $role->load('permissions'),
        ]);
    }
}