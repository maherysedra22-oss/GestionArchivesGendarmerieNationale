<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DestinationController extends Controller
{
    /**
     * Liste des destinations actives.
     */
    public function index()
    {
        $destinations = Destination::where('actif', true)
            ->orderBy('lib_officiel_desti')
            ->get();

        return response()->json($destinations);
    }

    /**
     * Créer une destination.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lib_officiel_desti' => [
                'required',
                'string',
                'max:255',
                'unique:destinations,lib_officiel_desti',
            ],
        ]);

        $destination = Destination::create([
            'lib_officiel_desti' => $validated['lib_officiel_desti'],
            'actif' => true,
        ]);

        return response()->json([
            'message' => 'Destination créée avec succès.',
            'data' => $destination,
        ], 201);
    }

    /**
     * Afficher une destination.
     */
    public function show(Destination $destination)
    {
        return response()->json($destination);
    }

    /**
     * Modifier une destination.
     */
    public function update(
        Request $request,
        Destination $destination
    ) {
        $validated = $request->validate([
            'lib_officiel_desti' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'destinations',
                    'lib_officiel_desti'
                )->ignore(
                    $destination->id_desti,
                    'id_desti'
                ),
            ],

            'actif' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $destination->update($validated);

        return response()->json([
            'message' => 'Destination modifiée avec succès.',
            'data' => $destination,
        ]);
    }

    /**
     * Désactiver une destination.
     */
    public function destroy(Destination $destination)
    {
        $destination->update([
            'actif' => false,
        ]);

        return response()->json([
            'message' => 'Destination supprimée avec succès.',
        ]);
    }
}