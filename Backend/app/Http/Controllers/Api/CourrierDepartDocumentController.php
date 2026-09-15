<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierDepart;
use App\Services\JournalActiviteService;
use Illuminate\Http\Request;

use App\Models\DocumentNumerique;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourrierDepartDocumentController extends Controller
{
    /**
     * Liste des documents associés à un courrier départ
     */
    public function index(int $id)
    {
        // 1. Rechercher le courrier départ
        $courrierDepart = CourrierDepart::with('documents')
            ->find($id);

        // 2. Vérifier si le courrier existe
        if (!$courrierDepart) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier départ introuvable.',
            ], 404);
        }

        // 3. Retourner les documents associés
        return response()->json([
            'success' => true,
            'message' => 'Documents du courrier départ récupérés avec succès.',
            'data' => $courrierDepart->documents,
        ]);
    }

    /**
     * Associer un document à un courrier départ
     */
    public function store(Request $request, int $id)
    {
        // 1. Rechercher le courrier départ
        $courrierDepart = CourrierDepart::find($id);

        if (!$courrierDepart) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier départ introuvable.',
            ], 404);
        }

        // 2. Valider le fichier
        $validated = $request->validate([
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240',
            ],
        ], [
            'document.required' => 'Le document est obligatoire.',
            'document.file' => 'Le fichier envoyé est invalide.',
            'document.mimes' => 'Le document doit être au format PDF, JPG, JPEG, PNG, DOC ou DOCX.',
            'document.max' => 'Le document ne doit pas dépasser 10 Mo.',
        ]);

        $file = $validated['document'];

        // 3. Calculer le checksum SHA-256
        $checksum = hash_file(
            'sha256',
            $file->getRealPath()
        );

        // 4. Vérifier si le même fichier existe déjà
        $documentExistant = DocumentNumerique::where(
            'checksum_sha256',
            $checksum
        )->first();

        if ($documentExistant) {

            // Vérifier s'il est déjà associé à ce courrier
            $dejaAssocie = $courrierDepart
                ->documents()
                ->where(
                    'documents_numeriques.num_doc',
                    $documentExistant->num_doc
                )
                ->exists();

            if ($dejaAssocie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce document est déjà associé à ce courrier départ.',
                ], 409);
            }

            // Associer le document existant
            $courrierDepart->documents()->attach(
                $documentExistant->num_doc
            );

            JournalActiviteService::enregistrer(
                'ATTACH',
                'documents_courriers_depart',
                $courrierDepart->num_ordre_dep,
                null,
                null,
                [
                    'num_ordre_dep' => $courrierDepart->num_ordre_dep,
                    'num_doc' => $documentExistant->num_doc,
                    'nom_original' => $documentExistant->nom_original,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Document existant associé au courrier départ avec succès.',
                'data' => [
                    'num_ordre_dep' => $courrierDepart->num_ordre_dep,
                    'document' => $documentExistant,
                ],
            ], 201);
        }

        // 5. Générer un nom de stockage unique
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $nomOriginal = $file->getClientOriginalName();

        $nomStockage = Str::uuid() . '.' . $extension;

        // 6. Enregistrer physiquement le fichier
        $chemin = $file->storeAs(
            'documents',
            $nomStockage,
            'public'
        );

        // 7. Créer l'enregistrement dans documents_numeriques
        $document = DocumentNumerique::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'extension' => $extension,
            'type_mime' => $file->getMimeType(),
            'taille' => $file->getSize(),
            'chemin' => $chemin,
            'checksum_sha256' => $checksum,
            'id_utilisateur_upload' => Auth::user()->id_utilisateur,
        ]);

        // 8. Associer le document au courrier départ
        $courrierDepart->documents()->attach(
            $document->num_doc
        );

        // 9. Journaliser l'action
        JournalActiviteService::enregistrer(
            'ATTACH',
            'documents_courriers_depart',
            $courrierDepart->num_ordre_dep,
            null,
            null,
            [
                'num_ordre_dep' => $courrierDepart->num_ordre_dep,
                'num_doc' => $document->num_doc,
                'nom_original' => $document->nom_original,
            ]
        );

        // 10. Retourner le document créé
        return response()->json([
            'success' => true,
            'message' => 'Document ajouté au courrier départ avec succès.',
            'data' => [
                'num_ordre_dep' => $courrierDepart->num_ordre_dep,
                'document' => $document,
            ],
        ], 201);
    }

    /**
     * Retirer un document d'un courrier départ
     */
    public function destroy(int $id, int $numDoc)
    {
        // 1. Rechercher le courrier départ
        $courrierDepart = CourrierDepart::find($id);

        if (!$courrierDepart) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier départ introuvable.',
            ], 404);
        }

        // 2. Rechercher le document associé
        $document = $courrierDepart
            ->documents()
            ->where(
                'documents_numeriques.num_doc',
                $numDoc
            )
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Ce document n\'est pas associé à ce courrier départ.',
            ], 404);
        }

        // 3. Sauvegarder les données avant suppression
        $donneesAvant = [
            'num_ordre_dep' => $courrierDepart->num_ordre_dep,
            'num_doc' => $document->num_doc,
            'nom_original' => $document->nom_original,
        ];

        // 4. Retirer uniquement la relation dans la table pivot
        $courrierDepart->documents()->detach(
            $numDoc
        );

        // 5. Journaliser l'action
        JournalActiviteService::enregistrer(
            'DETACH',
            'documents_courriers_depart',
            $courrierDepart->num_ordre_dep,
            null,
            $donneesAvant,
            null
        );

        // 6. Retourner la réponse
        return response()->json([
            'success' => true,
            'message' => 'Document retiré du courrier départ avec succès.',
            'data' => [
                'num_ordre_dep' => $courrierDepart->num_ordre_dep,
                'document' => $donneesAvant,
            ],
        ]);
    }

    /**
     * Télécharger / prévisualiser un document d'un courrier départ
     */
    public function download(int $id, int $numDoc)
    {
        // 1. Vérifier que le courrier départ existe
        $courrierDepart = CourrierDepart::find($id);

        if (!$courrierDepart) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier départ introuvable.',
            ], 404);
        }

        // 2. Rechercher le document associé au courrier
        $document = $courrierDepart
            ->documents()
            ->where('documents_numeriques.num_doc', $numDoc)
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document introuvable ou non associé à ce courrier départ.',
            ], 404);
        }

        // 3. Vérifier le fichier physique
        $chemin = storage_path('app/public/' . $document->chemin);

        if (!file_exists($chemin)) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier physique est introuvable.',
                'chemin' => $document->chemin,
            ], 404);
        }

        // 4. Afficher le document dans le navigateur
        return response()->file(
            $chemin,
            [
                'Content-Type' => $document->type_mime,
                'Content-Disposition' => 'inline; filename="' . $document->nom_original . '"',
            ]
        );
    }
}