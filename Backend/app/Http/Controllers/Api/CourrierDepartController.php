<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierDepart;
use App\Models\NatureCourrierDepart;
use App\Models\Classement;
use App\Services\JournalActiviteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourrierDepartController extends Controller
{
    public function index(Request $request)
    {
        $query = CourrierDepart::with([
            'nature',
            'classement',
            'utilisateurCreation',
            'destinations',
            'documents',
        ]);

        // Recherche globale
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ILIKE', "%{$search}%")
                    ->orWhere('objet_courr_dep', 'ILIKE', "%{$search}%");
            });
        }

        // Filtre priorité
        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        // Filtre nature
        if ($request->filled('num_nat')) {
            $query->where('num_nat', $request->num_nat);
        }

        // Filtre classement
        if ($request->filled('id_class')) {
            $query->where('id_class', $request->id_class);
        }

        // Filtre date début
        if ($request->filled('date_debut')) {
            $query->whereDate('date_dep', '>=', $request->date_debut);
        }

        // Filtre date fin
        if ($request->filled('date_fin')) {
            $query->whereDate('date_dep', '<=', $request->date_fin);
        }

        // Tri sécurisé
        $allowedSorts = [
            'num_ordre_dep',
            'reference',
            'date_dep',
            'priorite',
            'created_at',
            'updated_at',
        ];

        $sort = $request->get('sort', 'created_at');

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->get('direction', 'desc'));

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = min(
            max((int) $request->get('per_page', 15), 1),
            100
        );

        $courriers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des courriers départ récupérée avec succès.',
            'data' => $courriers,
        ]);
    }


    /**
     * Créer un courrier départ
     */
    public function store(Request $request)
    {
        // Validation des données reçues
        $validated = $request->validate([
            'reference' => [
                'required',
                'string',
                'max:30',
                'unique:courriers_depart,reference',
            ],

            'date_dep' => [
                'nullable',
                'date',
            ],

            'num_nat' => [
                'required',
                'integer',
                Rule::exists('natures_courriers_depart', 'num_nat')
                    ->where('actif', true),
            ],

            'objet_courr_dep' => [
                'required',
                'string',
            ],

            'id_class' => [
                'required',
                'integer',
                Rule::exists('classements', 'id_class')
                    ->where(function ($query) {
                        $query->where('type_courrier', 'DEPART')
                            ->where('actif', true);
                    }),
            ],

            'priorite' => [
                'nullable',
                Rule::in([
                    'NORMAL',
                    'URGENT',
                    'TRES_URGENT',
                ]),
            ],

            'observations' => [
                'nullable',
                'string',
            ],
        ]);

        // L'utilisateur connecté est automatiquement enregistré
        $validated['id_utilisateur_creation'] = Auth::id();

        // Valeur par défaut de la priorité
        if (!isset($validated['priorite'])) {
            $validated['priorite'] = 'NORMAL';
        }

        // Création du courrier départ
        $courrier = CourrierDepart::create($validated);

        // Chargement des relations
        $courrier->load([
            'nature',
            'classement',
            'utilisateurCreation',
            'destinations',
            'documents',
        ]);

        // Journalisation de la création
        JournalActiviteService::enregistrer(
            'CREATE',
            'courriers_depart',
            $courrier->num_ordre_dep,
            $courrier->reference,
            null,
            $courrier->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Courrier départ créé avec succès.',
            'data' => $courrier,
        ], 201);
    }

    /**
 * Afficher un courrier départ
 */
    public function show(int $id)
    {
    $courrierDepart = CourrierDepart::with([
        'nature',
        'classement',
        'utilisateurCreation',
        'destinations',
        'documents',
    ])->find($id);

    if (!$courrierDepart) {
        return response()->json([
            'success' => false,
            'message' => 'Courrier départ introuvable.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Courrier départ récupéré avec succès.',
        'data' => $courrierDepart,
    ]);
    }

     /**
  * Modifier un courrier départ
  */
    public function update(Request $request, int $id)
    {
    $courrierDepart = CourrierDepart::find($id);

    if (!$courrierDepart) {
        return response()->json([
            'success' => false,
            'message' => 'Courrier départ introuvable.',
        ], 404);
    }

    // Données avant modification
    $donneesAvant = $courrierDepart->toArray();

    // Validation
    $validated = $request->validate([
        'reference' => [
            'sometimes',
            'required',
            'string',
            'max:30',
            Rule::unique('courriers_depart', 'reference')
                ->ignore($courrierDepart->num_ordre_dep, 'num_ordre_dep'),
        ],

        'date_dep' => [
            'sometimes',
            'required',
            'date',
        ],

        'num_nat' => [
            'sometimes',
            'required',
            'integer',
            Rule::exists('natures_courriers_depart', 'num_nat')
                ->where('actif', true),
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
            Rule::exists('classements', 'id_class')
                ->where(function ($query) {
                    $query->where('type_courrier', 'DEPART')
                        ->where('actif', true);
                }),
        ],

        'priorite' => [
            'sometimes',
            'required',
            Rule::in([
                'NORMAL',
                'URGENT',
                'TRES_URGENT',
            ]),
        ],

        'observations' => [
            'nullable',
            'string',
        ],
    ]);

    // Mise à jour
    $courrierDepart->update($validated);

    // Recharger les relations
    $courrierDepart->load([
        'nature',
        'classement',
        'utilisateurCreation',
        'destinations',
        'documents',
    ]);

    // Données après modification
    $donneesApres = $courrierDepart->toArray();

    // Journalisation UPDATE
    JournalActiviteService::enregistrer(
        'UPDATE',
        'courriers_depart',
        $courrierDepart->num_ordre_dep,
        $courrierDepart->reference,
        $donneesAvant,
        $donneesApres
    );

    return response()->json([
        'success' => true,
        'message' => 'Courrier départ modifié avec succès.',
        'data' => $courrierDepart,
    ]);
    }

    /**
 * Supprimer un courrier départ
 */
    public function destroy(int $id)
    {
    $courrierDepart = CourrierDepart::find($id);

    if (!$courrierDepart) {
        return response()->json([
            'success' => false,
            'message' => 'Courrier départ introuvable.',
        ], 404);
    }

    // Conserver les informations avant suppression
    $donneesAvant = $courrierDepart->toArray();

    $idEnregistrement = $courrierDepart->num_ordre_dep;
    $referenceObjet = $courrierDepart->reference;

    // Suppression logique grâce à SoftDeletes
    $courrierDepart->delete();

    // Journalisation DELETE
    JournalActiviteService::enregistrer(
        'DELETE',
        'courriers_depart',
        $idEnregistrement,
        $referenceObjet,
        $donneesAvant,
        null
    );

    return response()->json([
        'success' => true,
        'message' => 'Courrier départ supprimé avec succès.',
    ]);
    }
}