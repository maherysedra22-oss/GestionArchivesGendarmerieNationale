<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierArrive;
use App\Models\DocumentNumerique;
use App\Services\JournalActiviteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CourrierArriveController extends Controller
{
    /**
     * ============================================================
     * LISTE DES COURRIERS ARRIVÉS
     * ============================================================
     */
    public function index(Request $request)
    {
        $query = CourrierArrive::with([
            'pieceSuite',
            'utilisateurCreation',
            'documents'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Recherche globale
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('num_ordre_orig', 'ILIKE', "%{$search}%")
                    ->orWhere('lib_orig', 'ILIKE', "%{$search}%")
                    ->orWhere('objet_courr_arri', 'ILIKE', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre priorité
        |--------------------------------------------------------------------------
        */
        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre statut
        |--------------------------------------------------------------------------
        */
        if ($request->filled('statut_dossier')) {
            $query->where(
                'statut_dossier',
                $request->statut_dossier
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre pièce de suite
        |--------------------------------------------------------------------------
        */
        if ($request->filled('id_piece_suit')) {
            $query->where(
                'id_piece_suit',
                $request->id_piece_suit
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre date début
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_debut')) {
            $query->whereDate(
                'date_enreg',
                '>=',
                $request->date_debut
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre date fin
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_fin')) {
            $query->whereDate(
                'date_enreg',
                '<=',
                $request->date_fin
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tri sécurisé
        |--------------------------------------------------------------------------
        */
        $allowedSorts = [
            'num_enreg_courr_arr',
            'date_enreg',
            'num_ordre_orig',
            'lib_orig',
            'priorite',
            'statut_dossier',
            'created_at',
        ];

        $sort = $request->get(
            'sort',
            'num_enreg_courr_arr'
        );

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'num_enreg_courr_arr';
        }

        /*
        |--------------------------------------------------------------------------
        | Direction du tri
        |--------------------------------------------------------------------------
        */
        $direction = strtolower(
            $request->get('direction', 'desc')
        );

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $perPage = (int) $request->get('per_page', 10);

        if ($perPage < 1) {
            $perPage = 10;
        }

        if ($perPage > 100) {
            $perPage = 100;
        }

        $courriers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des courriers arrivés récupérée avec succès.',
            'data' => $courriers,
        ]);
    }


    /**
     * ============================================================
     * CRÉER UN COURRIER ARRIVÉ
     * ============================================================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'num_ordre_orig' => [
                'required',
                'string',
                'max:100',
            ],

            'lib_orig' => [
                'required',
                'string',
                'max:255',
            ],

            'objet_courr_arri' => [
                'required',
                'string',
            ],

            'id_piece_suit' => [
                'required',
                'integer',
                'exists:pieces_suite,id_piece_suit',
            ],

            /*
            |--------------------------------------------------------------------------
            | Priorité
            |--------------------------------------------------------------------------
            */
            'priorite' => [
                'nullable',
                Rule::in([
                    'NORMAL',
                    'URGENT',
                    'TRES_URGENT',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Statut
            |--------------------------------------------------------------------------
            | IMPORTANT :
            | PostgreSQL accepte exactement :
            | En cours
            | Lecture
            | Archivé
            |--------------------------------------------------------------------------
            */
            'statut_dossier' => [
                'nullable',
                Rule::in([
                    'En cours',
                    'Lecture',
                    'Archivé',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Documents facultatifs
            |--------------------------------------------------------------------------
            */
            'documents' => [
                'nullable',
                'array',
            ],

            'documents.*' => [
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Création du courrier
            |--------------------------------------------------------------------------
            */
            $courrier = CourrierArrive::create([
                'num_ordre_orig' => $validated['num_ordre_orig'],
                'lib_orig' => $validated['lib_orig'],
                'objet_courr_arri' => $validated['objet_courr_arri'],

                'id_piece_suit' => $validated['id_piece_suit'],

                'id_utilisateur_creation' => Auth::id(),

                'priorite' => $validated['priorite']
                    ?? 'NORMAL',

                'statut_dossier' => $validated['statut_dossier']
                    ?? 'En cours',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ajouter les documents s'ils existent
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('documents')) {

                foreach ($request->file('documents') as $file) {

                    /*
                    |--------------------------------------------------------------------------
                    | Informations du fichier
                    |--------------------------------------------------------------------------
                    */
                    $nomOriginal = $file->getClientOriginalName();

                    $extension = strtolower(
                        $file->getClientOriginalExtension()
                    );

                    $typeMime = $file->getMimeType();

                    $taille = $file->getSize();

                    /*
                    |--------------------------------------------------------------------------
                    | Calcul checksum SHA-256
                    |--------------------------------------------------------------------------
                    */
                    $checksumSha256 = hash_file(
                        'sha256',
                        $file->getRealPath()
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Nom physique unique
                    |--------------------------------------------------------------------------
                    */
                    $nomStockage = Str::uuid()->toString()
                        . '.'
                        . $extension;

                    /*
                    |--------------------------------------------------------------------------
                    | Dossier de stockage
                    |--------------------------------------------------------------------------
                    */
                    $dossier = 'documents/courriers-arrives/'
                        . $courrier->num_enreg_courr_arr;

                    $chemin = $dossier . '/' . $nomStockage;

                    /*
                    |--------------------------------------------------------------------------
                    | Stockage physique
                    |--------------------------------------------------------------------------
                    */
                    Storage::disk('public')->putFileAs(
                        $dossier,
                        $file,
                        $nomStockage
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Création document numérique
                    |--------------------------------------------------------------------------
                    */
                    $document = DocumentNumerique::create([
                        'nom_original' => $nomOriginal,
                        'nom_stockage' => $nomStockage,
                        'extension' => $extension,
                        'type_mime' => $typeMime,
                        'taille' => $taille,
                        'chemin' => $chemin,
                        'checksum_sha256' => $checksumSha256,
                        'id_utilisateur_upload' => Auth::id(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Association courrier ↔ document
                    |--------------------------------------------------------------------------
                    */
                    $courrier->documents()->attach(
                        $document->num_doc
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Journalisation CREATE
            |--------------------------------------------------------------------------
            */
            JournalActiviteService::enregistrer(
                'CREATE',
                'courriers_arrives',
                $courrier->num_enreg_courr_arr,
                'COR_ARR' . str_pad(
                    (string) $courrier->num_enreg_courr_arr,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                null,
                $courrier->toArray()
            );

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Charger les relations
            |--------------------------------------------------------------------------
            */
            $courrier->load([
                'pieceSuite',
                'utilisateurCreation',
                'documents',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Courrier arrivé créé avec succès.',
                'data' => $courrier,
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du courrier arrivé.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * ============================================================
     * AFFICHER UN COURRIER ARRIVÉ
     * ============================================================
     */
    public function show(int $id)
    {
        $courrier = CourrierArrive::with([
            'pieceSuite',
            'utilisateurCreation',
            'documents',
        ])->find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Courrier arrivé récupéré avec succès.',
            'data' => $courrier,
        ]);
    }


    /**
     * ============================================================
     * MODIFIER UN COURRIER ARRIVÉ
     * ============================================================
     */
    public function update(Request $request, int $id)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        $validated = $request->validate([
            'num_ordre_orig' => [
                'required',
                'string',
                'max:100',
            ],

            'lib_orig' => [
                'required',
                'string',
                'max:255',
            ],

            'objet_courr_arri' => [
                'required',
                'string',
            ],

            'id_piece_suit' => [
                'required',
                'integer',
                'exists:pieces_suite,id_piece_suit',
            ],

            'priorite' => [
                'required',
                Rule::in([
                    'NORMAL',
                    'URGENT',
                    'TRES_URGENT',
                ]),
            ],

            'statut_dossier' => [
                'required',
                Rule::in([
                    'En cours',
                    'Lecture',
                    'Archivé',
                ]),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Données avant modification
        |--------------------------------------------------------------------------
        */
        $donneesAvant = $courrier->toArray();

        /*
        |--------------------------------------------------------------------------
        | Modification
        |--------------------------------------------------------------------------
        */
        $courrier->update([
            'num_ordre_orig' => $validated['num_ordre_orig'],
            'lib_orig' => $validated['lib_orig'],
            'objet_courr_arri' => $validated['objet_courr_arri'],
            'id_piece_suit' => $validated['id_piece_suit'],
            'priorite' => $validated['priorite'],
            'statut_dossier' => $validated['statut_dossier'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Journalisation UPDATE
        |--------------------------------------------------------------------------
        */
        JournalActiviteService::enregistrer(
            'UPDATE',
            'courriers_arrives',
            $courrier->num_enreg_courr_arr,
            'COR_ARR' . str_pad(
                (string) $courrier->num_enreg_courr_arr,
                2,
                '0',
                STR_PAD_LEFT
            ),
            $donneesAvant,
            $courrier->toArray()
        );

        $courrier->load([
            'pieceSuite',
            'utilisateurCreation',
            'documents',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Courrier arrivé modifié avec succès.',
            'data' => $courrier,
        ]);
    }


    /**
     * ============================================================
     * MODIFIER UNIQUEMENT LE STATUT
     * ============================================================
     */
    public function updateStatut(Request $request, int $id)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        $validated = $request->validate([
            'statut_dossier' => [
                'required',
                Rule::in([
                    'En cours',
                    'Lecture',
                    'Archivé',
                ]),
            ],
        ]);

        $ancienStatut = $courrier->statut_dossier;

        $courrier->update([
            'statut_dossier' => $validated['statut_dossier'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Journalisation changement statut
        |--------------------------------------------------------------------------
        */
        JournalActiviteService::enregistrer(
            'UPDATE_STATUT',
            'courriers_arrives',
            $courrier->num_enreg_courr_arr,
            'COR_ARR' . str_pad(
                (string) $courrier->num_enreg_courr_arr,
                2,
                '0',
                STR_PAD_LEFT
            ),
            [
                'statut_dossier' => $ancienStatut,
            ],
            [
                'statut_dossier' => $courrier->statut_dossier,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Statut du courrier arrivé modifié avec succès.',
            'data' => $courrier,
        ]);
    }


    /**
     * ============================================================
     * AJOUTER DES DOCUMENTS À UN COURRIER EXISTANT
     * ============================================================
     */
    public function ajouterDocuments(Request $request, int $id)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        $validated = $request->validate([
            'documents' => [
                'required',
                'array',
                'min:1',
            ],

            'documents.*' => [
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        $documentsAjoutes = [];

        DB::beginTransaction();

        try {

            foreach ($validated['documents'] as $file) {

                $nomOriginal = $file->getClientOriginalName();

                $extension = strtolower(
                    $file->getClientOriginalExtension()
                );

                $typeMime = $file->getMimeType();

                $taille = $file->getSize();

                /*
                |--------------------------------------------------------------------------
                | SHA-256
                |--------------------------------------------------------------------------
                */
                $checksumSha256 = hash_file(
                    'sha256',
                    $file->getRealPath()
                );

                /*
                |--------------------------------------------------------------------------
                | Vérifier si le même document existe déjà
                |--------------------------------------------------------------------------
                */
                $documentExistant = DocumentNumerique::where(
                    'checksum_sha256',
                    $checksumSha256
                )->first();

                if ($documentExistant) {

                    /*
                    |--------------------------------------------------------------------------
                    | Vérifier si déjà associé à ce courrier
                    |--------------------------------------------------------------------------
                    */
                    $dejaAssocie = $courrier->documents()
                        ->where(
                            'documents_numeriques.num_doc',
                            $documentExistant->num_doc
                        )
                        ->exists();

                    if (!$dejaAssocie) {

                        $courrier->documents()->attach(
                            $documentExistant->num_doc
                        );

                        $documentsAjoutes[] = $documentExistant;

                        JournalActiviteService::enregistrer(
                            'ATTACH',
                            'documents_courriers_arrives',
                            $courrier->num_enreg_courr_arr,
                            'COR_ARR' . str_pad(
                                (string) $courrier->num_enreg_courr_arr,
                                2,
                                '0',
                                STR_PAD_LEFT
                            ),
                            null,
                            [
                                'num_doc' => $documentExistant->num_doc,
                            ]
                        );
                    }

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Nouveau nom physique
                |--------------------------------------------------------------------------
                */
                $nomStockage = Str::uuid()->toString()
                    . '.'
                    . $extension;

                $dossier = 'documents/courriers-arrives/'
                    . $courrier->num_enreg_courr_arr;

                $chemin = $dossier . '/' . $nomStockage;

                /*
                |--------------------------------------------------------------------------
                | Stockage
                |--------------------------------------------------------------------------
                */
                Storage::disk('public')->putFileAs(
                    $dossier,
                    $file,
                    $nomStockage
                );

                /*
                |--------------------------------------------------------------------------
                | Création document
                |--------------------------------------------------------------------------
                */
                $document = DocumentNumerique::create([
                    'nom_original' => $nomOriginal,
                    'nom_stockage' => $nomStockage,
                    'extension' => $extension,
                    'type_mime' => $typeMime,
                    'taille' => $taille,
                    'chemin' => $chemin,
                    'checksum_sha256' => $checksumSha256,
                    'id_utilisateur_upload' => Auth::id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Association
                |--------------------------------------------------------------------------
                */
                $courrier->documents()->attach(
                    $document->num_doc
                );

                $documentsAjoutes[] = $document;

                /*
                |--------------------------------------------------------------------------
                | Journalisation
                |--------------------------------------------------------------------------
                */
                JournalActiviteService::enregistrer(
                    'ADD_DOCUMENT',
                    'documents_courriers_arrives',
                    $courrier->num_enreg_courr_arr,
                    'COR_ARR' . str_pad(
                        (string) $courrier->num_enreg_courr_arr,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ),
                    null,
                    [
                        'num_doc' => $document->num_doc,
                        'nom_original' => $document->nom_original,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documents ajoutés au courrier arrivé avec succès.',
                'data' => $documentsAjoutes,
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’ajout des documents.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

/**
 * ============================================================
 * STATISTIQUES DES COURRIERS ARRIVÉS
 * ============================================================
 */
    public function statistiques()
    {
    try {
        $total = CourrierArrive::count();

        $tresUrgent = CourrierArrive::where(
            'priorite',
            'TRES_URGENT'
        )->count();

        $urgent = CourrierArrive::where(
            'priorite',
            'URGENT'
        )->count();

        $archives = CourrierArrive::where(
            'statut_dossier',
            'Archivé'
        )->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistiques des courriers arrivés récupérées avec succès.',
            'data' => [
                'total' => $total,
                'tres_urgent' => $tresUrgent,
                'urgent' => $urgent,
                'archives' => $archives,
            ],
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du chargement des statistiques.',
            'error' => $e->getMessage(),
        ], 500);
    }
    }


    /**
     * ============================================================
     * SUPPRIMER / ARCHIVER LE COURRIER
     * ============================================================
     */
    public function destroy(int $id)
    {
        $courrier = CourrierArrive::find($id);

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier arrivé introuvable.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Données avant suppression
        |--------------------------------------------------------------------------
        */
        $donneesAvant = $courrier->toArray();

        /*
        |--------------------------------------------------------------------------
        | Soft delete
        |--------------------------------------------------------------------------
        */
        $courrier->delete();

        /*
        |--------------------------------------------------------------------------
        | Journalisation DELETE
        |--------------------------------------------------------------------------
        */
        JournalActiviteService::enregistrer(
            'DELETE',
            'courriers_arrives',
            $courrier->num_enreg_courr_arr,
            'COR_ARR' . str_pad(
                (string) $courrier->num_enreg_courr_arr,
                2,
                '0',
                STR_PAD_LEFT
            ),
            $donneesAvant,
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Courrier arrivé supprimé avec succès.',
        ]);
    }
}