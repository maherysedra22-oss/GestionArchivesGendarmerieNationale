<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierArrive;
use App\Models\DocumentNumerique;
use App\Services\JournalActiviteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;

class DocumentCourrierArriveController extends Controller
{
    /**
     * Liste des documents associés à un courrier arrivé.
     */
    public function index(int $id)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        $documents = $courrier
            ->documents()
            ->with('utilisateurUpload')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Documents du courrier arrivé récupérés avec succès.',
            'data' => $documents,
        ]);
    }

    /**
     * Afficher un document associé à un courrier arrivé.
     */
    public function view(int $id, int $numDoc)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        $document = $courrier
            ->documents()
            ->where(
                'documents_numeriques.num_doc',
                $numDoc
            )
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document non associé à ce courrier.',
            ], 404);
        }

        if (!Storage::disk('public')->exists($document->chemin)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier physique introuvable.',
            ], 404);
        }

        $filePath = Storage::disk('public')->path(
            $document->chemin
        );

        return response()->file($filePath, [
            'Content-Type' => $document->type_mime
                ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Upload et association d'un document à un courrier arrivé.
     */
    public function store(Request $request, int $id)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        $validated = $request->validate([
            'document' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:10240',
            ],
        ], [
            'document.required' => 'Veuillez sélectionner un document.',
            'document.file' => 'Le fichier envoyé est invalide.',
            'document.mimes' => 'Formats autorisés : PDF, DOC, DOCX, JPG, JPEG, PNG.',
            'document.max' => 'La taille maximale autorisée est de 10 Mo.',
        ]);

        $file = $validated['document'];

        /*
         * Informations du fichier
         */
        $nomOriginal = $file->getClientOriginalName();
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );
        $typeMime = $file->getMimeType();
        $taille = $file->getSize();

        /*
         * Calcul du checksum SHA-256
         */
        $checksum = hash_file(
            'sha256',
            $file->getRealPath()
        );

        /*
         * Vérifier si un document identique existe déjà.
         */
        $documentExistant = DocumentNumerique::where(
            'checksum_sha256',
            $checksum
        )->first();

        if ($documentExistant) {

            /*
             * Vérifier si ce document est déjà associé
             * à ce courrier arrivé.
             */
            $dejaAssocie = $courrier
                ->documents()
                ->where(
                    'documents_numeriques.num_doc',
                    $documentExistant->num_doc
                )
                ->exists();

            if ($dejaAssocie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce document est déjà associé à ce courrier arrivé.',
                ], 409);
            }

            /*
             * Le document existe mais n'est pas encore
             * associé à ce courrier.
             *
             * On réutilise le document existant.
             */
            $courrier->documents()->attach(
                $documentExistant->num_doc
            );

            JournalActiviteService::enregistrer(
                'ATTACH',
                'documents_courriers_arrives',
                $courrier->num_enreg_courr_arr,
                null,
                null,
                [
                    'num_enreg_courr_arr' => $courrier->num_enreg_courr_arr,
                    'num_doc' => $documentExistant->num_doc,
                    'nom_original' => $documentExistant->nom_original,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Document existant associé au courrier arrivé avec succès.',
                'data' => [
                    'num_enreg_courr_arr' => $courrier->num_enreg_courr_arr,
                    'document' => $documentExistant,
                ],
            ], 201);
        }

        /*
         * Nom unique du fichier
         */
        $nomStockage = Str::uuid()->toString() . '.' . $extension;

        /*
         * Dossier de stockage
         */
        $dossier = 'documents/courriers-arrives/'
            . $courrier->num_enreg_courr_arr;

        /*
         * Stockage du fichier
         */
        $chemin = $file->storeAs(
            $dossier,
            $nomStockage,
            'public'
        );

        /*
         * Création du document numérique
         */
        $document = DocumentNumerique::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'extension' => $extension,
            'type_mime' => $typeMime,
            'taille' => $taille,
            'chemin' => $chemin,
            'checksum_sha256' => $checksum,
            'id_utilisateur_upload' => Auth::id(),
        ]);

        /*
         * Association document ↔ courrier arrivé
         */
        $courrier->documents()->attach(
            $document->num_doc
        );

        /*
         * Journalisation de l'association
         */
        JournalActiviteService::enregistrer(
            'ATTACH',
            'documents_courriers_arrives',
            $courrier->num_enreg_courr_arr,
            null,
            null,
            [
                'num_enreg_courr_arr' => $courrier->num_enreg_courr_arr,
                'num_doc' => $document->num_doc,
                'nom_original' => $document->nom_original,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Document ajouté au courrier arrivé avec succès.',
            'data' => [
                'num_enreg_courr_arr' => $courrier->num_enreg_courr_arr,
                'document' => $document,
                'url' => asset(
                    'storage/' . $document->chemin
                ),
            ],
        ], 201);
    }

    /**
     * Télécharger un document associé à un courrier arrivé.
     */
    public function download(int $id, int $numDoc)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        /*
         * Récupérer uniquement le document
         * associé à ce courrier.
         */
        $document = $courrier
            ->documents()
            ->where(
                'documents_numeriques.num_doc',
                $numDoc
            )
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Ce document n\'est pas associé à ce courrier arrivé.',
            ], 404);
        }

        /*
         * Vérifier que le fichier physique existe.
         */
        if (!Storage::disk('public')->exists($document->chemin)) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier physique est introuvable.',
            ], 404);
        }

        /*
         * Journalisation du téléchargement.
         */
        $reference = 'COR_ARR' . str_pad(
            $courrier->num_enreg_courr_arr,
            2,
            '0',
            STR_PAD_LEFT
        );

        JournalActiviteService::enregistrer(
            'DOWNLOAD',
            'courriers_arrives',
            (int) $courrier->num_enreg_courr_arr,
            $reference,
            null,
            [
                'num_enreg_courr_arr' => $courrier->num_enreg_courr_arr,
                'num_doc' => $document->num_doc,
                'nom_original' => $document->nom_original,
                'download_at' => now()->format('Y-m-d H:i:s.u'),
            ]
        );
        /*
         * Récupérer le chemin physique.
         */
        $filePath = Storage::disk('public')->path(
            $document->chemin
        );

        /*
         * Télécharger le fichier.
         */
        return response()->download(
            $filePath,
            $document->nom_original,
            [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    /**
     * Retirer un document d'un courrier arrivé.
     *
     * IMPORTANT :
     * Cette méthode retire uniquement la relation
     * dans la table pivot.
     *
     * Le document numérique reste conservé.
     */
    public function destroy(int $id, int $numDoc)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        /*
         * Récupérer le document associé.
         */
        $document = $courrier
            ->documents()
            ->where(
                'documents_numeriques.num_doc',
                $numDoc
            )
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Ce document n\'est pas associé à ce courrier arrivé.',
            ], 404);
        }

        /*
         * Données avant suppression de la relation.
         */
        $donneesAvant = [
            'num_enreg_courr_arr' => $courrier->num_enreg_courr_arr,
            'num_doc' => $document->num_doc,
            'nom_original' => $document->nom_original,
        ];

        /*
         * Retirer uniquement l'association.
         */
        $courrier->documents()->detach(
            $document->num_doc
        );

        /*
         * Journalisation.
         */
        Log::info('DOWNLOAD ARRIVE - CONTROLLER ATTEINT', [
            'id' => $id,
            'numDoc' => $numDoc,
        ]);

        JournalActiviteService::enregistrer(
            'DETACH',
            'documents_courriers_arrives',
            $courrier->num_enreg_courr_arr,
            null,
            $donneesAvant,
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Document retiré du courrier arrivé avec succès.',
            'data' => [
                'num_enreg_courr_arr' => $courrier->num_enreg_courr_arr,
                'document' => $donneesAvant,
            ],
        ]);
    }
}
