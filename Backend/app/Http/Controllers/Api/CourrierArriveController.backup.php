<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierArrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourrierArriveController extends Controller
{
    public function index(Request $request)
    {
    $query = CourrierArrive::with([
        'pieceSuite',
        'classement',
        'utilisateurCreation',
        'documents'
    ]);

    // 🔎 Recherche globale
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('reference', 'ILIKE', "%{$search}%")
              ->orWhere('num_ordre_orig', 'ILIKE', "%{$search}%")
              ->orWhere('lib_orig', 'ILIKE', "%{$search}%")
              ->orWhere('objet_courr_arri', 'ILIKE', "%{$search}%");
        });
    }

    // 🎯 Filtre priorité
    if ($request->filled('priorite')) {
        $query->where('priorite', $request->priorite);
    }

    // 📁 Filtre classement
    if ($request->filled('id_class')) {
        $query->where('id_class', $request->id_class);
    }

    // 📅 Filtre date début
    if ($request->filled('date_debut')) {
        $query->whereDate('date_enreg', '>=', $request->date_debut);
    }

    // 📅 Filtre date fin
    if ($request->filled('date_fin')) {
        $query->whereDate('date_enreg', '<=', $request->date_fin);
    }

    // 🔢 Tri sécurisé
    $allowedSorts = [
        'num_enreg_courr_arr',
        'reference',
        'date_enreg',
        'num_ordre_orig',
        'lib_orig',
        'priorite',
        'created_at',
    ];

    $sortBy = $request->get('sort_by', 'created_at');

    if (!in_array($sortBy, $allowedSorts)) {
        $sortBy = 'created_at';
    }

    $sortDirection = strtolower(
        $request->get('sort_direction', 'desc')
    );

    if (!in_array($sortDirection, ['asc', 'desc'])) {
        $sortDirection = 'desc';
    }

    $query->orderBy($sortBy, $sortDirection);

    // 📄 Pagination
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


    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => [
                'required',
                'string',
                'max:30',
                'unique:courriers_arrives,reference',
            ],

            'date_enreg' => [
                'required',
                'date',
            ],

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

            'id_class' => [
                'required',
                'integer',
                'exists:classements,id_class',
            ],

            'priorite' => [
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

        $validated['id_utilisateur_creation'] = Auth::id();

        $courrier = CourrierArrive::create($validated);

        $courrier->load([
            'pieceSuite',
            'classement',
            'utilisateurCreation',
            'documents',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Courrier arrivé créé avec succès.',
            'data' => $courrier,
        ], 201);
    }

    public function show(int $courriers_arrife)
    {
    $courrier = CourrierArrive::with([
        'pieceSuite',
        'classement',
        'utilisateurCreation',
        'documents',
    ])->findOrFail($courriers_arrife);

    return response()->json([
        'success' => true,
        'message' => 'Courrier arrivé récupéré avec succès.',
        'data' => $courrier,
    ]);
    }

    public function update(Request $request, int $courriers_arrife)
    {
    $courrier = CourrierArrive::findOrFail($courriers_arrife);

    $validated = $request->validate([
        'reference' => [
            'required',
            'string',
            'max:30',
            Rule::unique('courriers_arrives', 'reference')
                ->ignore(
                    $courriers_arrife,
                    'num_enreg_courr_arr'
                ),
        ],

        'date_enreg' => [
            'required',
            'date',
        ],

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

        'id_class' => [
            'required',
            'integer',
            'exists:classements,id_class',
        ],

        'priorite' => [
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

    $courrier->update($validated);

    $courrier->load([
        'pieceSuite',
        'classement',
        'utilisateurCreation',
        'documents',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Courrier arrivé modifié avec succès.',
        'data' => $courrier,
    ]); 
    }

    public function destroy(int $courriers_arrife)
    {
    $courrier = CourrierArrive::findOrFail($courriers_arrife);

    $courrier->delete();

    return response()->json([
        'success' => true,
        'message' => 'Courrier arrivé supprimé avec succès.',
    ]);
    }
}