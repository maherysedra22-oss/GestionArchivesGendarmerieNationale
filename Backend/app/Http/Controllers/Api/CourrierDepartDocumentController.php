<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierDepart;
use App\Services\JournalActiviteService;
use Illuminate\Http\Request;

class CourrierDepartDocumentController extends Controller
{
    public function index(int $id)
    {
        // 1. Mitady ny courrier départ
        $courrierDepart = CourrierDepart::with('documents')
            ->find($id);

        // 2. Raha tsy hita
        if (!$courrierDepart) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier départ introuvable.',
            ], 404);
        }

        // 3. Mamerina ireo documents
        return response()->json([
            'success' => true,
            'message' => 'Documents du courrier départ récupérés avec succès.',
            'data' => $courrierDepart->documents,
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

        // 2. Validation du document
        $validated = $request->validate([
            'num_doc' => [
                'required',
                'integer',
                'exists:documents_numeriques,num_doc',
            ],
        ]);

        // 3. Vérifier si le document est déjà associé
        $dejaAssocie = $courrierDepart
            ->documents()
            ->where(
                'documents_numeriques.num_doc',
                $validated['num_doc']
            )
            ->exists();

        if ($dejaAssocie) {
            return response()->json([
                'success' => false,
                'message' => 'Ce document est déjà associé à ce courrier départ.',
            ], 409);
        }

        // 4. Ajouter la relation
        $courrierDepart->documents()->attach(
            $validated['num_doc']
        );

        // 5. Récupérer le document ajouté
        $document = $courrierDepart
            ->documents()
            ->where(
                'documents_numeriques.num_doc',
                $validated['num_doc']
            )
            ->first();

        // 6. Journaliser l'action
        JournalActiviteService::enregistrer(
            'ATTACH',
            'documents_courriers_depart',
            $courrierDepart->num_ordre_dep,
            $courrierDepart->reference,
            null,
            [
                'num_ordre_dep' => $courrierDepart->num_ordre_dep,
                'num_doc' => $document->num_doc,
                'nom_original' => $document->nom_original,
            ]
        );

        // 7. Réponse
        return response()->json([
            'success' => true,
            'message' => 'Document ajouté au courrier départ avec succès.',
            'data' => [
                'courrier_depart' => $courrierDepart->reference,
                'document' => $document,
            ],
        ], 201);
    }

    public function destroy(int $id, int $numDoc)
    {
    // 1. Mitady ny courrier départ
    $courrierDepart = CourrierDepart::find($id);

    if (!$courrierDepart) {
        return response()->json([
            'success' => false,
            'message' => 'Courrier départ introuvable.',
        ], 404);
    }

    // 2. Mitady ilay document associé
    $document = $courrierDepart
        ->documents()
        ->where('documents_numeriques.num_doc', $numDoc)
        ->first();

    if (!$document) {
        return response()->json([
            'success' => false,
            'message' => 'Ce document n\'est pas associé à ce courrier départ.',
        ], 404);
    }

    // 3. Mitahiry ny données alohan'ny suppression
    $donneesAvant = [
        'num_ordre_dep' => $courrierDepart->num_ordre_dep,
        'num_doc' => $document->num_doc,
        'nom_original' => $document->nom_original,
    ];

    // 4. Manala ny relation ao amin'ny table pivot
    $courrierDepart->documents()->detach($numDoc);

    // 5. Journaliser l'action
    JournalActiviteService::enregistrer(
        'DETACH',
        'documents_courriers_depart',
        $courrierDepart->num_ordre_dep,
        $courrierDepart->reference,
        $donneesAvant,
        null
    );

    // 6. Réponse
    return response()->json([
        'success' => true,
        'message' => 'Document retiré du courrier départ avec succès.',
        'data' => [
            'courrier_depart' => $courrierDepart->reference,
            'document' => $donneesAvant,
        ],
    ]);
    }
}