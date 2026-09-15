<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PieceSuite;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PieceSuiteController extends Controller
{
    /**
     * Liste des pièces de suite
     */
    public function index(Request $request)
    {
        $query = PieceSuite::query();

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(
                'nom_piece_suit',
                'ILIKE',
                '%' . $search . '%'
            );
        }

        // Filtre actif
        if ($request->has('actif')) {
            $query->where(
                'actif',
                filter_var($request->actif, FILTER_VALIDATE_BOOLEAN)
            );
        }

        $pieces = $query
            ->orderBy('nom_piece_suit')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des pièces de suite récupérée avec succès.',
            'data' => $pieces,
        ]);
    }

    /**
     * Ajouter une pièce de suite
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_piece_suit' => [
                'required',
                'string',
                'max:255',
                'unique:pieces_suite,nom_piece_suit',
            ],
            'actif' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $piece = PieceSuite::create([
            'nom_piece_suit' => $validated['nom_piece_suit'],
            'actif' => $validated['actif'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pièce de suite ajoutée avec succès.',
            'data' => $piece,
        ], 201);
    }

    /**
     * Afficher une pièce
     */
    public function show(int $id)
    {
    $pieceSuite = PieceSuite::findOrFail($id);

    return response()->json([
        'success' => true,
        'message' => 'Pièce de suite récupérée avec succès.',
        'data' => $pieceSuite,
    ]);
    }

    /**
     * Modifier une pièce
     */
    public function update(Request $request, int $id)
    {
    $pieceSuite = PieceSuite::findOrFail($id);

    $validated = $request->validate([
        'nom_piece_suit' => [
            'required',
            'string',
            'max:255',
            Rule::unique('pieces_suite', 'nom_piece_suit')
                ->ignore(
                    $pieceSuite->id_piece_suit,
                    'id_piece_suit'
                ),
        ],

        'actif' => [
            'sometimes',
            'boolean',
        ],
    ]);

    $pieceSuite->update([
        'nom_piece_suit' => $validated['nom_piece_suit'],
        'actif' => $validated['actif'] ?? $pieceSuite->actif,
    ]);

    $pieceSuite->refresh();

    return response()->json([
        'success' => true,
        'message' => 'Pièce de suite modifiée avec succès.',
        'data' => $pieceSuite,
    ]);
    }

    /**
     * Supprimer une pièce
     */
    public function destroy( int $id)
    {
    $pieceSuite = PieceSuite::findOrFail($id);

    try {

        $pieceSuite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pièce de suite supprimée avec succès.',
        ]);

    } catch (\Illuminate\Database\QueryException $e) {

        return response()->json([
            'success' => false,
            'message' => 'Impossible de supprimer cette pièce de suite car elle est déjà utilisée par un courrier arrivé.',
        ], 409);
    }
    }
}