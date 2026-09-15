<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalActivite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalActiviteController extends Controller
{
    /**
     * Liste des activités journalisées.
     */
    public function index(Request $request): JsonResponse
    {
        $query = JournalActivite::query()
            ->with('utilisateur')
            ->orderByDesc('created_at');

        // =========================================================
        // RECHERCHE GLOBALE
        // =========================================================
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('nom_utilisateur', 'ILIKE', "%{$search}%")
                    ->orWhere('action', 'ILIKE', "%{$search}%")
                    ->orWhere('table_concernee', 'ILIKE', "%{$search}%")
                    ->orWhere('reference_objet', 'ILIKE', "%{$search}%")
                    ->orWhere('adresse_ip', 'ILIKE', "%{$search}%");
            });
        }

        // =========================================================
        // FILTRE UTILISATEUR
        // =========================================================
        if ($request->filled('id_utilisateur')) {
            $query->where(
                'id_utilisateur',
                $request->input('id_utilisateur')
            );
        }

        // =========================================================
        // FILTRE ACTION
        // =========================================================
        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->input('action')
            );
        }

        // =========================================================
        // FILTRE TABLE CONCERNEE
        // =========================================================
        if ($request->filled('table_concernee')) {
            $query->where(
                'table_concernee',
                $request->input('table_concernee')
            );
        }

        // =========================================================
        // DATE DEBUT
        // =========================================================
        if ($request->filled('date_debut')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_debut')
            );
        }

        // =========================================================
        // DATE FIN
        // =========================================================
        if ($request->filled('date_fin')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_fin')
            );
        }

        // =========================================================
        // STATISTIQUES GLOBALES SUR LES RESULTATS FILTRES
        // =========================================================
        $statisticsQuery = clone $query;

        $totalActivites = (clone $statisticsQuery)->count();

        $utilisateursActifs = (clone $statisticsQuery)
            ->whereNotNull('id_utilisateur')
            ->distinct('id_utilisateur')
            ->count('id_utilisateur');

        $tablesConcernees = (clone $statisticsQuery)
            ->whereNotNull('table_concernee')
            ->distinct('table_concernee')
            ->count('table_concernee');

        // =========================================================
        // PAGINATION
        // =========================================================
        $perPage = (int) $request->input('per_page', 15);

        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $journal = $query->paginate($perPage);

        // =========================================================
        // REPONSE
        // =========================================================
        return response()->json([
            'success' => true,

            'message' => 'Journal des activités récupéré avec succès.',

            'data' => $journal->items(),

            'pagination' => [
                'current_page' => $journal->currentPage(),
                'last_page' => $journal->lastPage(),
                'per_page' => $journal->perPage(),
                'total' => $journal->total(),
                'from' => $journal->firstItem(),
                'to' => $journal->lastItem(),
            ],

            'statistics' => [
                'total_activites' => $totalActivites,
                'utilisateurs_actifs' => $utilisateursActifs,
                'tables_concernees' => $tablesConcernees,
            ],
        ]);
    }

    /**
     * Détail d'une activité.
     */
    public function show(int $id): JsonResponse
    {
        $journal = JournalActivite::with('utilisateur')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Activité récupérée avec succès.',
            'data' => $journal,
        ]);
    }
}