<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierDepart;
use Illuminate\Http\Request;
use App\Services\JournalActiviteService;

class CourrierDepartDestinationController extends Controller
{
    public function index(int $id)
    {
        $courrierDepart = CourrierDepart::with('destinations')
            ->find($id);

        if (!$courrierDepart) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier départ introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Destinations du courrier départ récupérées avec succès.',
            'data' => $courrierDepart->destinations,
        ]);
    }

    public function store(Request $request, int $id)
    {
    // 1. Mitady ny courrier départ
    $courrierDepart = CourrierDepart::find($id);

    if (!$courrierDepart) {
        return response()->json([
            'success' => false,
            'message' => 'Courrier départ introuvable.',
        ], 404);
    }

    // 2. Validation de la destination
    $validated = $request->validate([
        'id_desti' => [
            'required',
            'integer',
            'exists:destinations,id_desti',
        ],
    ]);

    // 3. Vérifier si la destination est déjà associée
    $dejaAssociee = $courrierDepart
        ->destinations()
        ->where('destinations.id_desti', $validated['id_desti'])
        ->exists();

    if ($dejaAssociee) {
        return response()->json([
            'success' => false,
            'message' => 'Cette destination est déjà associée à ce courrier départ.',
        ], 409);
    }

    // 4. Ajouter la relation dans la table destiner
    $courrierDepart->destinations()->attach(
        $validated['id_desti']
    );

    // 5. Récupérer la destination ajoutée
    $destination = $courrierDepart
        ->destinations()
        ->where('destinations.id_desti', $validated['id_desti'])
        ->first();

    // 6. Journaliser l'action
    JournalActiviteService::enregistrer(
        'ATTACH',
        'destiner',
        $courrierDepart->num_ordre_dep,
        $courrierDepart->reference,
        null,
        [
            'num_ordre_dep' => $courrierDepart->num_ordre_dep,
            'id_desti' => $destination->id_desti,
            'destination' => $destination->lib_officiel_desti,
        ]
    );

    // 7. Réponse
    return response()->json([
        'success' => true,
        'message' => 'Destination ajoutée au courrier départ avec succès.',
        'data' => [
            'courrier_depart' => $courrierDepart->reference,
            'destination' => $destination,
        ],
    ], 201);
    }

    public function destroy(int $id, int $idDesti)
    {
    // 1. Mitady ny courrier départ
    $courrierDepart = CourrierDepart::find($id);

    if (!$courrierDepart) {
        return response()->json([
            'success' => false,
            'message' => 'Courrier départ introuvable.',
        ], 404);
    }

    // 2. Mitady ny destination mifandray amin'ilay courrier
    $destination = $courrierDepart
        ->destinations()
        ->where('destinations.id_desti', $idDesti)
        ->first();

    if (!$destination) {
        return response()->json([
            'success' => false,
            'message' => 'Cette destination n\'est pas associée à ce courrier départ.',
        ], 404);
    }

    // 3. Maka ny données alohan'ny suppression
    $donneesAvant = [
        'num_ordre_dep' => $courrierDepart->num_ordre_dep,
        'id_desti' => $destination->id_desti,
        'destination' => $destination->lib_officiel_desti,
    ];

    // 4. Esorina ny relation ao amin'ny table destiner
    $courrierDepart->destinations()->detach($idDesti);

    // 5. Journaliser l'action
    JournalActiviteService::enregistrer(
        'DETACH',
        'destiner',
        $courrierDepart->num_ordre_dep,
        $courrierDepart->reference,
        $donneesAvant,
        null
    );

    // 6. Réponse
    return response()->json([
        'success' => true,
        'message' => 'Destination retirée du courrier départ avec succès.',
        'data' => [
            'courrier_depart' => $courrierDepart->reference,
            'destination' => $destination,
        ],
    ]);
    }
}