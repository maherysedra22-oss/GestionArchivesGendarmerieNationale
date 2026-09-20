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

class DashboardController extends Controller
{
    /**
     * Afficher les données du tableau de bord.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | STATISTIQUES COURRIERS ARRIVÉS
            |--------------------------------------------------------------------------
            */

            $totalCourriersArrives = CourrierArrive::count();

            $courriersUrgents = CourrierArrive::where(
                'priorite',
                'URGENT'
            )->count();

            $courriersTresUrgents = CourrierArrive::where(
                'priorite',
                'TRES_URGENT'
            )->count();
            
            $courriersNormaux = CourrierArrive::where(
                'priorite',
                'NORMAL'
            )->count();

            $courriersArchives = CourrierArrive::where(
                'statut_dossier',
                'Archivé'
            )->count();
            
            $courriersEnCours = CourrierArrive::where(
                'statut_dossier',
                'En cours'
            )->count();
            
            $courriersLecture = CourrierArrive::where(
                'statut_dossier',
                'Lecture'
            )->count();


            /*
            |--------------------------------------------------------------------------
            | STATISTIQUES COURRIERS DÉPART
            |--------------------------------------------------------------------------
            */

            $totalCourriersDepart = CourrierDepart::count();


            /*
            |--------------------------------------------------------------------------
            | STATISTIQUES DOCUMENTS
            |--------------------------------------------------------------------------
            */

            $totalDocuments = DocumentNumerique::count();


            /*
            |--------------------------------------------------------------------------
            | STATISTIQUES UTILISATEURS
            |--------------------------------------------------------------------------
            */

            $totalUtilisateurs = Utilisateur::count();


            /*
            |--------------------------------------------------------------------------
            | DERNIÈRES ACTIVITÉS
            |--------------------------------------------------------------------------
            |
            | JournalActivite utilise :
            | - primary key : id_journal
            | - created_at   : date de création
            |
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
            | EVOLUTION DES COURRIERS (6 derniers mois)
            |--------------------------------------------------------------------------
            */
            $months = [];
            $arrivesData = [];
            $departData = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthLabel = $date->translatedFormat('M Y');
                $months[] = ucfirst($monthLabel);
                
                $arrivesData[] = CourrierArrive::whereYear('created_at', $date->year)
                                               ->whereMonth('created_at', $date->month)
                                               ->count();
                                               
                $departData[] = CourrierDepart::whereYear('created_at', $date->year)
                                              ->whereMonth('created_at', $date->month)
                                              ->count();
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
                    'statistiques' => [
                        'courriers_arrives' => $totalCourriersArrives,
                        'courriers_depart' => $totalCourriersDepart,
                        'documents' => $totalDocuments,
                        'utilisateurs' => $totalUtilisateurs,
                        'urgents' => $courriersUrgents,
                        'tres_urgents' => $courriersTresUrgents,
                        'archives' => $courriersArchives,
                        'en_cours' => $courriersEnCours,
                        'lecture' => $courriersLecture,
                    ],

                    'statuts' => [
                        'en_cours' => $courriersEnCours,
                        'lecture' => $courriersLecture,
                        'archives' => $courriersArchives,
                    ],

                    'priorites' => [
                        'normal' => $courriersNormaux,
                        'urgent' => $courriersUrgents,
                        'tres_urgent' => $courriersTresUrgents,
                    ],

                    'evolution' => [
                        'labels' => $months,
                        'arrives' => $arrivesData,
                        'depart' => $departData,
                    ],

                    'activites' => $activites,
                ],
            ]);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | GESTION DES ERREURS
            |--------------------------------------------------------------------------
            */

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