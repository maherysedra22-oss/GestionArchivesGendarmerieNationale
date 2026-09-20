<?php

namespace App\Services;

use App\Models\JournalActivite;
use Illuminate\Support\Facades\Auth;
use Throwable;

class JournalActiviteService
{
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

                'action' => $action,
                'table_concernee' => $tableConcernee,
                'id_enregistrement' => $idEnregistrement,
                'reference_objet' => $referenceObjet,
                'donnees_avant' => $donneesAvant,
                'donnees_apres' => $donneesApres,
                'adresse_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}