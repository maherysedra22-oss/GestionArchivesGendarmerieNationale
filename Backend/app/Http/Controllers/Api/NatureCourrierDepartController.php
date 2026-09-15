<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NatureCourrierDepart;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NatureCourrierDepartController extends Controller
{
    /**
     * Liste des natures actives.
     */
    public function index()
    {
        $natures = NatureCourrierDepart::where('actif', true)
            ->orderBy('nom_nature')
            ->get();

        return response()->json($natures);
    }

    /**
     * Créer une nouvelle nature.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_nature' => [
                'required',
                'string',
                'max:150',
                'unique:natures_courriers_depart,nom_nature',
            ],
        ]);

        $nature = NatureCourrierDepart::create([
            'nom_nature' => $validated['nom_nature'],
            'actif' => true,
        ]);

        return response()->json([
            'message' => 'Nature créée avec succès.',
            'data' => $nature,
        ], 201);
    }

    /**
     * Afficher une nature.
     */
    public function show(NatureCourrierDepart $natures_courriers_depart)
    {
        return response()->json(
            $natures_courriers_depart
        );
    }

    /**
     * Modifier une nature.
     */
    public function update(
        Request $request,
        NatureCourrierDepart $natures_courriers_depart
    ) {
        $validated = $request->validate([
            'nom_nature' => [
                'required',
                'string',
                'max:150',
                Rule::unique(
                    'natures_courriers_depart',
                    'nom_nature'
                )->ignore(
                    $natures_courriers_depart->num_nat,
                    'num_nat'
                ),
            ],
            'actif' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $natures_courriers_depart->update($validated);

        return response()->json([
            'message' => 'Nature modifiée avec succès.',
            'data' => $natures_courriers_depart,
        ]);
    }

    /**
     * Désactiver une nature.
     */
    public function destroy(
        NatureCourrierDepart $natures_courriers_depart
    ) {
        $natures_courriers_depart->update([
            'actif' => false,
        ]);

        return response()->json([
            'message' => 'Nature supprimée avec succès.',
        ]);
    }
}