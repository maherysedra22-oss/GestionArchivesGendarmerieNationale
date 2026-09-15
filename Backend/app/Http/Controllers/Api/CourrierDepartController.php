<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierDepart;
use App\Models\Destination;
use App\Services\JournalActiviteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourrierDepartController extends Controller
{
    /**
     * ============================================================
     * LISTE DES COURRIERS DEPART
     * ============================================================
     */
    public function index(Request $request)
    {
        $query = CourrierDepart::with([
            'nature',
            'classement',
            'utilisateurCreation',
            'destinations',
            'documents',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Recherche par objet
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(
                'objet_courr_dep',
                'ILIKE',
                "%{$search}%"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre par nature
        |--------------------------------------------------------------------------
        */
        if ($request->filled('num_nat')) {

            $query->where(
                'num_nat',
                $request->num_nat
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre par classement
        |--------------------------------------------------------------------------
        */
        if ($request->filled('id_class')) {

            $query->where(
                'id_class',
                $request->id_class
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtre date début
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_debut')) {

            $query->whereDate(
                'date_dep',
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
                'date_dep',
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
            'num_ordre_dep',
            'date_dep',
            'created_at',
            'updated_at',
        ];

        $sort = $request->get(
            'sort',
            'created_at'
        );

        if (!in_array($sort, $allowedSorts)) {

            $sort = 'created_at';
        }

        $direction = strtolower(
            $request->get(
                'direction',
                'desc'
            )
        );

        if (!in_array(
            $direction,
            ['asc', 'desc']
        )) {

            $direction = 'desc';
        }

        $query->orderBy(
            $sort,
            $direction
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $perPage = min(
            max(
                (int) $request->get(
                    'per_page',
                    15
                ),
                1
            ),
            100
        );

        $courriers = $query->paginate(
            $perPage
        );

        return response()->json([

            'success' => true,

            'message' =>
                'Liste des courriers départ récupérée avec succès.',

            'data' => $courriers,

        ]);
    }


    /**
     * ============================================================
     * CREER UN COURRIER DEPART
     * ============================================================
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([

            /*
            |------------------------------------------------------
            | Informations courrier
            |------------------------------------------------------
            */
            'num_ordre_dep' => [
                'required',
                'integer',
                'min:1',
                'unique:courriers_depart,num_ordre_dep',
            ],

            'date_dep' => [
                'nullable',
                'date',
            ],

            'num_nat' => [
                'required',
                'integer',

                Rule::exists(
                    'natures_courriers_depart',
                    'num_nat'
                )->where(
                    'actif',
                    true
                ),
            ],

            'objet_courr_dep' => [
                'required',
                'string',
            ],

            'id_class' => [
                'required',
                'integer',

                Rule::exists(
                    'classements',
                    'id_class'
                )->where(function ($query) {

                    $query
                        ->where(
                            'type_courrier',
                            'DEPART'
                        )
                        ->where(
                            'actif',
                            true
                        );
                }),
            ],


            /*
            |------------------------------------------------------
            | Destinations existantes
            |------------------------------------------------------
            */

            'destinations' => [
                'nullable',
                'array',
            ],

            'destinations.*' => [
                'integer',

                Rule::exists(
                    'destinations',
                    'id_desti'
                )->where(
                    'actif',
                    true
                ),
            ],


            /*
            |------------------------------------------------------
            | Nouvelles destinations
            |------------------------------------------------------
            */

            'nouvelles_destinations' => [
                'nullable',
                'array',
            ],

            'nouvelles_destinations.*' => [
                'string',
                'max:255',
            ],


            /*
            |------------------------------------------------------
            | Documents existants
            |------------------------------------------------------
            */

            'documents' => [
                'nullable',
                'array',
            ],

            'documents.*' => [
                'integer',

                Rule::exists(
                    'documents_numeriques',
                    'num_doc'
                ),
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Vérifier qu'au moins une destination existe
        |--------------------------------------------------------------------------
        */

        $destinationsExistantes =
            $validated['destinations'] ?? [];

        $nouvellesDestinations =
            $validated['nouvelles_destinations'] ?? [];

        if (
            empty($destinationsExistantes)
            && empty($nouvellesDestinations)
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Veuillez sélectionner ou ajouter au moins une destination.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Création courrier
        |--------------------------------------------------------------------------
        */

        $courrier = CourrierDepart::create([

            'num_ordre_dep' =>
                $validated['num_ordre_dep'],

            'date_dep' =>
                $validated['date_dep']
                ?? now()->toDateString(),

            'num_nat' =>
                $validated['num_nat'],

            'objet_courr_dep' =>
                $validated['objet_courr_dep'],

            'id_class' =>
                $validated['id_class'],

            'id_utilisateur_creation' =>
                Auth::id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Destinations existantes
        |--------------------------------------------------------------------------
        */

        $destinationIds =
            $validated['destinations'] ?? [];


        /*
        |--------------------------------------------------------------------------
        | Création nouvelles destinations
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $validated['nouvelles_destinations']
            )
        ) {

            foreach (
                $validated['nouvelles_destinations']
                as $nomDestination
            ) {

                $nomDestination =
                    trim($nomDestination);

                if ($nomDestination === '') {
                    continue;
                }

                $destination =
                    Destination::firstOrCreate(

                        [
                            'lib_officiel_desti' =>
                                $nomDestination,
                        ],

                        [
                            'actif' => true,
                        ]

                    );

                $destinationIds[] =
                    $destination->id_desti;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Association destinations
        |--------------------------------------------------------------------------
        */

        $destinationIds =
            array_unique(
                $destinationIds
            );

        if (!empty($destinationIds)) {

            $courrier
                ->destinations()
                ->sync(
                    $destinationIds
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Association documents
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $validated['documents']
            )
        ) {

            $courrier
                ->documents()
                ->sync(
                    array_unique(
                        $validated['documents']
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Chargement relations
        |--------------------------------------------------------------------------
        */

        $courrier->load([

            'nature',

            'classement',

            'utilisateurCreation',

            'destinations',

            'documents',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Journalisation
        |--------------------------------------------------------------------------
        */

        JournalActiviteService::enregistrer(

            'CREATE',

            'courriers_depart',

            $courrier->num_ordre_dep,

            null,

            null,

            $courrier->toArray()

        );


        /*
        |--------------------------------------------------------------------------
        | Réponse
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' =>
                'Courrier départ créé avec succès.',

            'data' =>
                $courrier,

        ], 201);
    }


    /**
     * ============================================================
     * AFFICHER UN COURRIER DEPART
     * ============================================================
     */
    public function show(int $id)
    {
        $courrierDepart =
            CourrierDepart::with([

                'nature',

                'classement',

                'utilisateurCreation',

                'destinations',

                'documents',

            ])->find($id);


        if (!$courrierDepart) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Courrier départ introuvable.',

            ], 404);
        }


        return response()->json([

            'success' => true,

            'message' =>
                'Courrier départ récupéré avec succès.',

            'data' =>
                $courrierDepart,

        ]);
    }


    /**
     * ============================================================
     * MODIFIER UN COURRIER DEPART
     * ============================================================
     */
    public function update(
        Request $request,
        int $id
    ) {

        /*
        |--------------------------------------------------------------------------
        | Recherche courrier
        |--------------------------------------------------------------------------
        */

        $courrierDepart =
            CourrierDepart::find($id);


        if (!$courrierDepart) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Courrier départ introuvable.',

            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Données avant modification
        |--------------------------------------------------------------------------
        */

        $donneesAvant =
            $courrierDepart
                ->load([

                    'nature',

                    'classement',

                    'destinations',

                    'documents',

                ])
                ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                /*
                |--------------------------------------------------
                | Informations courrier
                |--------------------------------------------------
                */

                'date_dep' => [
                    'sometimes',
                    'required',
                    'date',
                ],

                'num_nat' => [
                    'sometimes',
                    'required',
                    'integer',

                    Rule::exists(
                        'natures_courriers_depart',
                        'num_nat'
                    )->where(
                        'actif',
                        true
                    ),
                ],

                'objet_courr_dep' => [
                    'sometimes',
                    'required',
                    'string',
                ],

                'id_class' => [
                    'sometimes',
                    'required',
                    'integer',

                    Rule::exists(
                        'classements',
                        'id_class'
                    )->where(function ($query) {

                        $query
                            ->where(
                                'type_courrier',
                                'DEPART'
                            )
                            ->where(
                                'actif',
                                true
                            );
                    }),
                ],


                /*
                |--------------------------------------------------
                | Destinations existantes
                |--------------------------------------------------
                */

                'destinations' => [
                    'sometimes',
                    'nullable',
                    'array',
                ],

                'destinations.*' => [
                    'integer',

                    Rule::exists(
                        'destinations',
                        'id_desti'
                    )->where(
                        'actif',
                        true
                    ),
                ],


                /*
                |--------------------------------------------------
                | Nouvelles destinations
                |--------------------------------------------------
                */

                'nouvelles_destinations' => [
                    'nullable',
                    'array',
                ],

                'nouvelles_destinations.*' => [
                    'string',
                    'max:255',
                ],


                /*
                |--------------------------------------------------
                | Documents
                |--------------------------------------------------
                */

                'documents' => [
                    'sometimes',
                    'nullable',
                    'array',
                ],

                'documents.*' => [
                    'integer',

                    Rule::exists(
                        'documents_numeriques',
                        'num_doc'
                    ),
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Mise à jour informations courrier
        |--------------------------------------------------------------------------
        */

        $courrierData =
            collect($validated)
                ->only([

                    'date_dep',

                    'num_nat',

                    'objet_courr_dep',

                    'id_class',

                ])
                ->toArray();


        if (!empty($courrierData)) {

            $courrierDepart->update(
                $courrierData
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mise à jour destinations
        |--------------------------------------------------------------------------
        */

        if (

            array_key_exists(
                'destinations',
                $validated
            )

            ||

            !empty(
                $validated[
                    'nouvelles_destinations'
                ] ?? []
            )

        ) {

            $destinationIds =
                $validated['destinations']
                ?? [];


            /*
            |------------------------------------------------------
            | Création nouvelles destinations
            |------------------------------------------------------
            */

            if (
                !empty(
                    $validated[
                        'nouvelles_destinations'
                    ] ?? []
                )
            ) {

                foreach (

                    $validated[
                        'nouvelles_destinations'
                    ]

                    as $nomDestination

                ) {

                    $nomDestination =
                        trim($nomDestination);


                    if ($nomDestination === '') {
                        continue;
                    }


                    $destination =
                        Destination::firstOrCreate(

                            [

                                'lib_officiel_desti'
                                    =>
                                    $nomDestination,

                            ],

                            [

                                'actif'
                                    =>
                                    true,

                            ]

                        );


                    $destinationIds[] =
                        $destination->id_desti;
                }
            }


            /*
            |------------------------------------------------------
            | Synchronisation destinations
            |------------------------------------------------------
            */

            $destinationIds =
                array_unique(
                    $destinationIds
                );


            if (!empty($destinationIds)) {

                $courrierDepart
                    ->destinations()
                    ->sync(
                        $destinationIds
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Mise à jour documents
        |--------------------------------------------------------------------------
        */

        if (

            array_key_exists(
                'documents',
                $validated
            )

        ) {

            $documentIds =
                $validated['documents']
                ?? [];


            $courrierDepart
                ->documents()
                ->sync(
                    array_unique(
                        $documentIds
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Recharger relations
        |--------------------------------------------------------------------------
        */

        $courrierDepart->load([

            'nature',

            'classement',

            'utilisateurCreation',

            'destinations',

            'documents',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Données après modification
        |--------------------------------------------------------------------------
        */

        $donneesApres =
            $courrierDepart
                ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Journalisation
        |--------------------------------------------------------------------------
        */

        JournalActiviteService::enregistrer(

            'UPDATE',

            'courriers_depart',

            $courrierDepart->num_ordre_dep,

            null,

            $donneesAvant,

            $donneesApres

        );


        /*
        |--------------------------------------------------------------------------
        | Réponse
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' =>
                'Courrier départ modifié avec succès.',

            'data' =>
                $courrierDepart,

        ]);
    }


    /**
     * ============================================================
     * SUPPRIMER UN COURRIER DEPART
     * ============================================================
     */
    public function destroy(int $id)
    {

        /*
        |--------------------------------------------------------------------------
        | Recherche courrier
        |--------------------------------------------------------------------------
        */

        $courrierDepart =
            CourrierDepart::find($id);


        if (!$courrierDepart) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Courrier départ introuvable.',

            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Données avant suppression
        |--------------------------------------------------------------------------
        */

        $donneesAvant =
            $courrierDepart
                ->load([

                    'nature',

                    'classement',

                    'utilisateurCreation',

                    'destinations',

                    'documents',

                ])
                ->toArray();


        $idEnregistrement =
            $courrierDepart
                ->num_ordre_dep;


        /*
        |--------------------------------------------------------------------------
        | Soft Delete
        |--------------------------------------------------------------------------
        */

        $courrierDepart->delete();


        /*
        |--------------------------------------------------------------------------
        | Journalisation
        |--------------------------------------------------------------------------
        */

        JournalActiviteService::enregistrer(

            'DELETE',

            'courriers_depart',

            $idEnregistrement,

            null,

            $donneesAvant,

            null

        );


        /*
        |--------------------------------------------------------------------------
        | Réponse
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' =>
                'Courrier départ supprimé avec succès.',

        ]);
    }
}