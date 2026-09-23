<?php

namespace App\Services;

use App\Models\JournalActivite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class JournalActiviteService
{
    /**
     * Enregistrer une activité dans le journal.
     *
     * Chaque appel crée une nouvelle ligne.
     * Il n'y a aucune déduplication, notamment pour DOWNLOAD.
     */
    public static function enregistrer(
        string $action,
        ?string $tableConcernee = null,
        ?int $idEnregistrement = null,
        ?string $referenceObjet = null,
        ?array $donneesAvant = null,
        ?array $donneesApres = null
    ): void {
        try {
            $utilisateur = Auth::user();

            JournalActivite::create([
                'id_utilisateur' => $utilisateur?->id_utilisateur,

                'nom_utilisateur' => $utilisateur
                    ? trim(
                        $utilisateur->nom . ' ' . $utilisateur->prenom
                    )
                    : null,

                'action' => strtoupper($action),

                'table_concernee' => $tableConcernee,

                'id_enregistrement' => $idEnregistrement,

                'reference_objet' => $referenceObjet,

                'donnees_avant' => $donneesAvant,

                'donnees_apres' => $donneesApres,

                'adresse_ip' => request()->ip(),

                'user_agent' => request()->userAgent(),
            ]);

        } catch (Throwable $e) {

            /*
             * En cas d'erreur, on conserve les informations
             * détaillées dans le fichier de log.
             */
            Log::error('ERREUR JOURNALISATION ACTIVITE', [
                'action' => strtoupper($action),
                'table_concernee' => $tableConcernee,
                'id_enregistrement' => $idEnregistrement,
                'reference_objet' => $referenceObjet,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            /*
             * L'exception n'est pas relancée pour ne pas bloquer
             * le processus principal (ex: téléchargement de document).
             */
        }
    }
}
