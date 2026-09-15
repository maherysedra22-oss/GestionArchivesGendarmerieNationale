<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassementController extends Controller
{
    /**
     * Liste des classements actifs.
     *
     * Par défaut, uniquement les classements DEPART.
     */
    public function index(Request $request)
    {
        $query = Classement::where('actif', true);

        $type = strtoupper(
            $request->query('type_courrier', 'DEPART')
        );

        if (in_array($type, ['ARRIVE', 'DEPART'])) {
            $query->where('type_courrier', $type);
        }

        $classements = $query
            ->orderBy('nom_class')
            ->get();

        return response()->json($classements);
    }

    /**
     * Créer un classement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_class' => [
                'required',
                'string',
                'max:150',
            ],

            'type_courrier' => [
                'required',
                Rule::in(['ARRIVE', 'DEPART']),
            ],
        ]);

        $classement = Classement::create([
            'nom_class' => $validated['nom_class'],
            'type_courrier' => $validated['type_courrier'],
            'actif' => true,
        ]);

        return response()->json([
            'message' => 'Classement créé avec succès.',
            'data' => $classement,
        ], 201);
    }

    /**
     * Afficher un classement.
     */
    public function show(Classement $classement)
    {
        return response()->json($classement);
    }

    /**
     * Modifier un classement.
     */
    public function update(
        Request $request,
        Classement $classement
    ) {
        $validated = $request->validate([
            'nom_class' => [
                'required',
                'string',
                'max:150',
            ],

            'type_courrier' => [
                'required',
                Rule::in(['ARRIVE', 'DEPART']),
            ],

            'actif' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $classement->update($validated);

        return response()->json([
            'message' => 'Classement modifié avec succès.',
            'data' => $classement,
        ]);
    }

    /**
     * Désactiver un classement.
     */
    public function destroy(Classement $classement)
    {
        $classement->update([
            'actif' => false,
        ]);

        return response()->json([
            'message' => 'Classement supprimé avec succès.',
        ]);
    }
}