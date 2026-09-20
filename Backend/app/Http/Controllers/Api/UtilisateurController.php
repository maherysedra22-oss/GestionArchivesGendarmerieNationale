<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradeMilitaire;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Throwable;

class UtilisateurController extends Controller
{
    /**
     * Liste des utilisateurs.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Utilisateur::query()
                ->with([
                    'role:id_role,nom_role,actif,systeme',
                    'grade:id_grade,nom_grade,ordre_hierarchique,categorie,insigne_symbole,actif',
                ])
                ->select([
                    'id_utilisateur',
                    'matricule',
                    'nom',
                    'prenom',
                    'poste_fonction',
                    'email',
                    'id_grade',
                    'id_role',
                    'statut',
                    'derniere_connexion',
                    'doit_changer_mdp',
                    'created_at',
                    'updated_at',
                ]);

            /**
             * Recherche globale.
             */
            if ($request->filled('search')) {
                $search = trim($request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('matricule', 'ILIKE', "%{$search}%")
                        ->orWhere('nom', 'ILIKE', "%{$search}%")
                        ->orWhere('prenom', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%")
                        ->orWhere(
                            'poste_fonction',
                            'ILIKE',
                            "%{$search}%"
                        );
                });
            }

            /**
             * Filtre rôle.
             */
            if ($request->filled('id_role')) {
                $query->where(
                    'id_role',
                    $request->integer('id_role')
                );
            }

            /**
             * Filtre grade.
             */
            if ($request->filled('id_grade')) {
                $query->where(
                    'id_grade',
                    $request->integer('id_grade')
                );
            }

            /**
             * Filtre statut.
             */
            if (
                $request->has('statut') &&
                $request->input('statut') !== ''
            ) {
                $statut = filter_var(
                    $request->input('statut'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                if ($statut !== null) {
                    $query->where('statut', $statut);
                }
            }

            /**
             * Filtre changement mot de passe.
             */
            if (
                $request->has('doit_changer_mdp') &&
                $request->input('doit_changer_mdp') !== ''
            ) {
                $doitChanger = filter_var(
                    $request->input('doit_changer_mdp'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                if ($doitChanger !== null) {
                    $query->where(
                        'doit_changer_mdp',
                        $doitChanger
                    );
                }
            }

            /**
             * Tri.
             */
            $allowedSorts = [
                'matricule',
                'nom',
                'prenom',
                'created_at',
                'derniere_connexion',
            ];

            $sort = $request->input(
                'sort',
                'created_at'
            );

            $direction = strtolower(
                $request->input(
                    'direction',
                    'desc'
                )
            );

            if (!in_array($sort, $allowedSorts, true)) {
                $sort = 'created_at';
            }

            if (!in_array($direction, ['asc', 'desc'], true)) {
                $direction = 'desc';
            }

            $query->orderBy($sort, $direction);

            /**
             * Pagination.
             */
            $perPage = min(
                max(
                    $request->integer('per_page', 15),
                    1
                ),
                100
            );

            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' =>
                    'Liste des utilisateurs récupérée avec succès.',
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de récupérer les utilisateurs.',
            ], 500);
        }
    }

    /**
     * Création d'un utilisateur.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matricule' => [
                'required',
                'string',
                'max:30',
                'unique:utilisateurs,matricule',
            ],

            'nom' => [
                'required',
                'string',
                'max:100',
            ],

            'prenom' => [
                'required',
                'string',
                'max:100',
            ],

            'poste_fonction' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
                'unique:utilisateurs,email',
            ],

            'id_grade' => [
                'required',
                'integer',
                Rule::exists(
                    'grades_militaires',
                    'id_grade'
                )->where('actif', true),
            ],

            'id_role' => [
                'required',
                'integer',
                Rule::exists(
                    'roles',
                    'id_role'
                )->where('actif', true),
            ],

            'statut' => [
                'sometimes',
                'boolean',
            ],

            'doit_changer_mdp' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'matricule.required' =>
                'Le matricule est obligatoire.',

            'matricule.unique' =>
                'Ce matricule existe déjà.',

            'nom.required' =>
                'Le nom est obligatoire.',

            'prenom.required' =>
                'Le prénom est obligatoire.',

            'poste_fonction.required' =>
                'Le poste/fonction est obligatoire.',

            'email.required' =>
                'L’adresse email est obligatoire.',

            'email.email' =>
                'L’adresse email est invalide.',

            'email.unique' =>
                'Cette adresse email existe déjà.',

            'mot_de_passe.min' =>
                'Le mot de passe doit contenir au moins 8 caractères.',

            'id_grade.required' =>
                'Le grade est obligatoire.',

            'id_grade.exists' =>
                'Le grade sélectionné est invalide ou inactif.',

            'id_role.required' =>
                'Le rôle est obligatoire.',

            'id_role.exists' =>
                'Le rôle sélectionné est invalide ou inactif.',
        ]);

        try {
            $user = DB::transaction(
                function () use ($validated) {

                    /**
                     * Si aucun mot de passe n'est fourni,
                     * génération automatique temporaire.
                     */
                    $temporaryPassword = 'Gendarmerie123';

                    $user = Utilisateur::create([
                        'matricule' =>
                            trim($validated['matricule']),

                        'nom' =>
                            trim($validated['nom']),

                        'prenom' =>
                            trim($validated['prenom']),

                        'poste_fonction' =>
                            trim($validated['poste_fonction']),

                        'email' =>
                            strtolower(
                                trim($validated['email'])
                            ),

                        'mot_de_passe' =>
                            Hash::make($temporaryPassword),

                        'id_grade' =>
                            $validated['id_grade'],

                        'id_role' =>
                            $validated['id_role'],

                        'statut' =>
                            $validated['statut'] ?? true,

                        'doit_changer_mdp' =>
                            $validated['doit_changer_mdp']
                            ?? true,
                    ]);

                    return $user;
                }
            );

            $user->load([
                'role:id_role,nom_role,actif,systeme',
                'grade:id_grade,nom_grade,ordre_hierarchique,categorie,insigne_symbole,actif',
            ]);

            return response()->json([
                'success' => true,
                'message' =>
                    'Utilisateur créé avec succès.',
                'data' => $user,
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de créer l’utilisateur.',
            ], 500);
        }
    }

    /**
     * Affichage d'un utilisateur.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Utilisateur::with([
                'role:id_role,nom_role,description,actif,systeme',
                'grade:id_grade,nom_grade,ordre_hierarchique,categorie,insigne_symbole,actif',
            ])->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Utilisateur introuvable.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' =>
                    'Utilisateur récupéré avec succès.',
                'data' => $user,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de récupérer cet utilisateur.',
            ], 500);
        }
    }

    /**
     * Modification d'un utilisateur.
     *
     * Le mot de passe n'est PAS modifié ici.
     * Pour modifier le mot de passe d'un utilisateur,
     * utiliser resetPassword().
     */
    public function update(
        Request $request,
        int $id
    ): JsonResponse {
        $user = Utilisateur::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Utilisateur introuvable.',
            ], 404);
        }

        $validated = $request->validate([
            'matricule' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'utilisateurs',
                    'matricule'
                )->ignore(
                    $user->id_utilisateur,
                    'id_utilisateur'
                ),
            ],

            'nom' => [
                'required',
                'string',
                'max:100',
            ],

            'prenom' => [
                'required',
                'string',
                'max:100',
            ],

            'poste_fonction' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique(
                    'utilisateurs',
                    'email'
                )->ignore(
                    $user->id_utilisateur,
                    'id_utilisateur'
                ),
            ],

            'id_grade' => [
                'required',
                'integer',
                Rule::exists(
                    'grades_militaires',
                    'id_grade'
                )->where('actif', true),
            ],

            'id_role' => [
                'required',
                'integer',
                Rule::exists(
                    'roles',
                    'id_role'
                )->where('actif', true),
            ],

            'statut' => [
                'sometimes',
                'boolean',
            ],

            'doit_changer_mdp' => [
                'sometimes',
                'boolean',
            ],
        ]);

        try {
            DB::transaction(
                function () use ($user, $validated) {

                    $user->update([
                        'matricule' =>
                            trim($validated['matricule']),

                        'nom' =>
                            trim($validated['nom']),

                        'prenom' =>
                            trim($validated['prenom']),

                        'poste_fonction' =>
                            trim($validated['poste_fonction']),

                        'email' =>
                            strtolower(
                                trim($validated['email'])
                            ),

                        'id_grade' =>
                            $validated['id_grade'],

                        'id_role' =>
                            $validated['id_role'],

                        'statut' =>
                            $validated['statut']
                            ?? $user->statut,

                        'doit_changer_mdp' =>
                            $validated['doit_changer_mdp']
                            ?? $user->doit_changer_mdp,
                    ]);
                }
            );

            $user->refresh();

            $user->load([
                'role:id_role,nom_role,description,actif,systeme',
                'grade:id_grade,nom_grade,ordre_hierarchique,categorie,insigne_symbole,actif',
            ]);

            return response()->json([
                'success' => true,
                'message' =>
                    'Utilisateur modifié avec succès.',
                'data' => $user,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de modifier cet utilisateur.',
            ], 500);
        }
    }

    /**
     * Suppression logique d'un utilisateur.
     */
    public function destroy(
        Request $request,
        int $id
    ): JsonResponse {
        $user = Utilisateur::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Utilisateur introuvable.',
            ], 404);
        }

        /**
         * Empêcher un utilisateur de supprimer
         * son propre compte.
         */
        if (
            $request->user() &&
            (int) $request->user()->id_utilisateur ===
                (int) $user->id_utilisateur
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Vous ne pouvez pas supprimer votre propre compte.',
            ], 422);
        }

        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' =>
                    'Utilisateur supprimé avec succès.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de supprimer cet utilisateur.',
            ], 500);
        }
    }

    /**
     * Activation / désactivation.
     */
    public function updateStatut(
        Request $request,
        int $id
    ): JsonResponse {
        $validated = $request->validate([
            'statut' => [
                'required',
                'boolean',
            ],
        ]);

        $utilisateur = Utilisateur::findOrFail($id);

        /**
         * Empêcher la désactivation de son propre compte.
         */
        if (
            $request->user() &&
            (int) $utilisateur->id_utilisateur ===
                (int) $request->user()->id_utilisateur
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Vous ne pouvez pas désactiver votre propre compte.',
            ], 422);
        }

        $utilisateur->statut =
            $validated['statut'];

        $utilisateur->save();

        return response()->json([
            'success' => true,
            'message' =>
                $validated['statut']
                    ? 'Utilisateur activé avec succès.'
                    : 'Utilisateur désactivé avec succès.',
            'data' => $utilisateur->load([
                'role',
                'grade',
            ]),
        ]);
    }

    /**
     * Réinitialisation du mot de passe
     * par l'administrateur.
     *
     * IMPORTANT :
     * - L'ancien mot de passe n'est jamais affiché.
     * - Le nouveau mot de passe est hashé.
     * - Le mot de passe n'est jamais retourné par l'API.
     * - L'utilisateur devra le changer après connexion.
     */
    public function resetPassword(
        Request $request,
        int $id
    ): JsonResponse {
        /**
         * Vérification de l'utilisateur connecté.
         */
        $admin = $request->user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Utilisateur non authentifié.',
            ], 401);
        }

        /**
         * Vérification du rôle Administrateur.
         */
        $admin->loadMissing('role');

        if (
            !$admin->role ||
            $admin->role->nom_role !== 'Administrateur'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Seul un administrateur peut réinitialiser un mot de passe.',
            ], 403);
        }

        /**
         * Recherche de l'utilisateur cible.
         */
        $user = Utilisateur::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Utilisateur introuvable.',
            ], 404);
        }

        /**
         * Validation du nouveau mot de passe.
         *
         * Le champ confirmé doit être :
         *
         * mot_de_passe_confirmation
         */
        $validated = $request->validate([
            'mot_de_passe' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],
        ], [
            'mot_de_passe.required' =>
                'Le nouveau mot de passe est obligatoire.',

            'mot_de_passe.min' =>
                'Le mot de passe doit contenir au moins 8 caractères.',

            'mot_de_passe.max' =>
                'Le mot de passe ne doit pas dépasser 255 caractères.',

            'mot_de_passe.confirmed' =>
                'La confirmation du mot de passe ne correspond pas.',
        ]);

        try {
            DB::transaction(
                function () use ($user, $validated) {

                    $user->update([
                        'mot_de_passe' =>
                            Hash::make(
                                $validated['mot_de_passe']
                            ),

                        /**
                         * Oblige l'utilisateur à modifier
                         * son mot de passe après connexion.
                         */
                        'doit_changer_mdp' => true,
                    ]);
                }
            );

            return response()->json([
                'success' => true,
                'message' =>
                    'Mot de passe réinitialisé avec succès.',

                'data' => [
                    'id_utilisateur' =>
                        $user->id_utilisateur,

                    'doit_changer_mdp' => true,
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de réinitialiser le mot de passe.',
            ], 500);
        }
    }



    /**
     * Changement du mot de passe par l'utilisateur connecté.
     *
     * Utilisé notamment lors de la première connexion
     * lorsque doit_changer_mdp = true.
     *
     * IMPORTANT :
     * - L'utilisateur ne fournit pas son ancien mot de passe.
     * - L'utilisateur doit être authentifié.
     * - Le nouveau mot de passe est hashé.
     * - doit_changer_mdp devient false après succès.
     */
    public function changePassword(Request $request): JsonResponse
    {
        /**
         * Vérification de l'utilisateur connecté.
         */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        /**
         * Validation du nouveau mot de passe.
         *
         * La règle "confirmed" attend :
         * mot_de_passe_confirmation
         */
        $validated = $request->validate([
            'mot_de_passe' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],
        ], [
            'mot_de_passe.required' =>
                'Le nouveau mot de passe est obligatoire.',

            'mot_de_passe.min' =>
                'Le mot de passe doit contenir au moins 8 caractères.',

            'mot_de_passe.max' =>
                'Le mot de passe ne doit pas dépasser 255 caractères.',

            'mot_de_passe.confirmed' =>
                'La confirmation du mot de passe ne correspond pas.',
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {

                $user->update([
                    'mot_de_passe' => Hash::make(
                        $validated['mot_de_passe']
                    ),

                    /**
                     * Le mot de passe initial/temporaire
                     * a maintenant été remplacé.
                     */
                    'doit_changer_mdp' => false,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Mot de passe modifié avec succès.',

                'data' => [
                    'id_utilisateur' =>
                        $user->id_utilisateur,

                    'doit_changer_mdp' => false,
                ],
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de modifier le mot de passe.',
            ], 500);
        }
    }

    /**
     * Liste des rôles actifs pour les formulaires.
     */
    public function roles(): JsonResponse
    {
        try {
            $roles = Role::query()
                ->where('actif', true)
                ->orderByDesc('systeme')
                ->orderBy('nom_role')
                ->get([
                    'id_role',
                    'nom_role',
                    'description',
                    'actif',
                    'systeme',
                ]);

            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de récupérer les rôles.',
            ], 500);
        }
    }

    /**
     * Liste des grades actifs.
     */
    public function grades(): JsonResponse
    {
        try {
            $grades = GradeMilitaire::query()
                ->where('actif', true)
                ->orderBy('ordre_hierarchique')
                ->get([
                    'id_grade',
                    'nom_grade',
                    'ordre_hierarchique',
                    'categorie',
                    'insigne_symbole',
                    'actif',
                ]);

            return response()->json([
                'success' => true,
                'data' => $grades,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de récupérer les grades.',
            ], 500);
        }
    }

}