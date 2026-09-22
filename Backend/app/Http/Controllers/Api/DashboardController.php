<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierArrive;
use App\Models\CourrierDepart;
use App\Models\DocumentNumerique;
use App\Models\Utilisateur;
use App\Models\JournalActivite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Afficher les données du tableau de bord.
     *
     * Paramètres facultatifs :
     * - mois : 1 à 12
     * - annee : année sélectionnée
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | VALIDATION MOIS / ANNÉE
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([
                'mois' => ['nullable', 'integer', 'between:1,12'],
                'annee' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            ]);

            $mois = (int) ($validated['mois'] ?? now()->month);
            $annee = (int) ($validated['annee'] ?? now()->year);

            /*
            |--------------------------------------------------------------------------
            | PÉRIODE SÉLECTIONNÉE
            |--------------------------------------------------------------------------
            */

            $dateDebut = Carbon::create($annee, $mois, 1)->startOfMonth();
            $dateFin = $dateDebut->copy()->endOfMonth();

            /*
            |--------------------------------------------------------------------------
            | COURRIERS ARRIVÉS
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | On utilise date_enreg et non created_at.
            |
            */

            $courriersArrivesQuery = CourrierArrive::query()
                ->whereDate('date_enreg', '>=', $dateDebut->toDateString())
                ->whereDate('date_enreg', '<=', $dateFin->toDateString());

            $totalCourriersArrives = (clone $courriersArrivesQuery)->count();

            $courriersNormaux = (clone $courriersArrivesQuery)
                ->where('priorite', 'NORMAL')
                ->count();

            $courriersUrgents = (clone $courriersArrivesQuery)
                ->where('priorite', 'URGENT')
                ->count();

            $courriersTresUrgents = (clone $courriersArrivesQuery)
                ->where('priorite', 'TRES_URGENT')
                ->count();

            $courriersArchives = (clone $courriersArrivesQuery)
                ->where('statut_dossier', 'Archivé')
                ->count();

            $courriersEnCours = (clone $courriersArrivesQuery)
                ->where('statut_dossier', 'En cours')
                ->count();

            $courriersLecture = (clone $courriersArrivesQuery)
                ->where('statut_dossier', 'Lecture')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | COURRIERS DÉPART
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | On utilise date_dep et non created_at.
            |
            */

            $courriersDepartQuery = CourrierDepart::query()
                ->whereDate('date_dep', '>=', $dateDebut->toDateString())
                ->whereDate('date_dep', '<=', $dateFin->toDateString());

            $totalCourriersDepart = (clone $courriersDepartQuery)->count();

            $courriersDepartNormaux = (clone $courriersDepartQuery)
                ->where('priorite', 'NORMAL')
                ->count();

            $courriersDepartUrgents = (clone $courriersDepartQuery)
                ->where('priorite', 'URGENT')
                ->count();

            $courriersDepartTresUrgents = (clone $courriersDepartQuery)
                ->where('priorite', 'TRES_URGENT')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | DOCUMENTS
            |--------------------------------------------------------------------------
            |
            | Les documents restent globaux.
            |
            */

            $totalDocuments = DocumentNumerique::count();

            /*
            |--------------------------------------------------------------------------
            | UTILISATEURS
            |--------------------------------------------------------------------------
            |
            | Les utilisateurs restent globaux.
            |
            */

            $totalUtilisateurs = Utilisateur::count();

            /*
            |--------------------------------------------------------------------------
            | DERNIÈRES ACTIVITÉS
            |--------------------------------------------------------------------------
            */

            $activites = JournalActivite::query()
                ->orderByDesc('id_journal')
                ->limit(8)
                ->get()
                ->map(function (JournalActivite $activite) {
                    return [
                        'id' => $activite->id_journal,
                        'action' => $activite->action,
                        'utilisateur' => $activite->nom_utilisateur,
                        'table_concernee' => $activite->table_concernee,
                        'id_enregistrement' => $activite->id_enregistrement,
                        'reference_objet' => $activite->reference_objet,
                        'date' => $activite->created_at,
                        'adresse_ip' => $activite->adresse_ip,
                    ];
                })
                ->values();

            /*
            |--------------------------------------------------------------------------
            | ÉVOLUTION JOURNALIÈRE
            |--------------------------------------------------------------------------
            */

            $nombreJours = $dateDebut->daysInMonth;

            /*
            |--------------------------------------------------------------------------
            | COURRIERS ARRIVÉS PAR JOUR
            |--------------------------------------------------------------------------
            |
            | PostgreSQL :
            | DATE(date_enreg) permet de regrouper par jour.
            |
            */

            $arrivesParJour = CourrierArrive::query()
                ->whereDate('date_enreg', '>=', $dateDebut->toDateString())
                ->whereDate('date_enreg', '<=', $dateFin->toDateString())
                ->selectRaw('DATE(date_enreg) as jour, COUNT(*) as total')
                ->groupBy(DB::raw('DATE(date_enreg)'))
                ->pluck('total', 'jour');

            /*
            |--------------------------------------------------------------------------
            | COURRIERS DÉPART PAR JOUR
            |--------------------------------------------------------------------------
            */

            $departParJour = CourrierDepart::query()
                ->whereDate('date_dep', '>=', $dateDebut->toDateString())
                ->whereDate('date_dep', '<=', $dateFin->toDateString())
                ->selectRaw('DATE(date_dep) as jour, COUNT(*) as total')
                ->groupBy(DB::raw('DATE(date_dep)'))
                ->pluck('total', 'jour');

            /*
            |--------------------------------------------------------------------------
            | CONSTRUCTION DES DONNÉES DU GRAPHIQUE
            |--------------------------------------------------------------------------
            */

            $labels = [];
            $arrivesData = [];
            $departData = [];

            for ($jour = 1; $jour <= $nombreJours; $jour++) {

                $date = $dateDebut->copy()->day($jour);

                $dateCle = $date->format('Y-m-d');

                $labels[] = str_pad(
                    (string) $jour,
                    2,
                    '0',
                    STR_PAD_LEFT
                );

                $arrivesData[] = (int) (
                    $arrivesParJour[$dateCle] ?? 0
                );

                $departData[] = (int) (
                    $departParJour[$dateCle] ?? 0
                );
            }

            /*
            |--------------------------------------------------------------------------
            | RÉPONSE JSON
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,

                'message' => 'Données du tableau de bord récupérées avec succès.',

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | STATISTIQUES PRINCIPALES
                    |--------------------------------------------------------------------------
                    */

                    'statistiques' => [
                        'courriers_arrives' => $totalCourriersArrives,
                        'courriers_depart' => $totalCourriersDepart,
                        'documents' => $totalDocuments,
                        'utilisateurs' => $totalUtilisateurs,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | STATUTS
                    |--------------------------------------------------------------------------
                    */

                    'statuts' => [
                        'en_cours' => $courriersEnCours,
                        'lecture' => $courriersLecture,
                        'archives' => $courriersArchives,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | PRIORITÉS COURRIERS ARRIVÉS
                    |--------------------------------------------------------------------------
                    */

                    'priorites' => [
                        'normal' => $courriersNormaux,
                        'urgent' => $courriersUrgents,
                        'tres_urgent' => $courriersTresUrgents,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | PRIORITÉS COURRIERS DÉPART
                    |--------------------------------------------------------------------------
                    */

                    'priorites_depart' => [
                        'normal' => $courriersDepartNormaux,
                        'urgent' => $courriersDepartUrgents,
                        'tres_urgent' => $courriersDepartTresUrgents,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | ÉVOLUTION
                    |--------------------------------------------------------------------------
                    */

                    'evolution' => [
                        'mois' => $mois,
                        'annee' => $annee,
                        'labels' => $labels,
                        'arrives' => $arrivesData,
                        'depart' => $departData,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | ACTIVITÉS
                    |--------------------------------------------------------------------------
                    */

                    'activites' => $activites,
                ],
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement du tableau de bord.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}