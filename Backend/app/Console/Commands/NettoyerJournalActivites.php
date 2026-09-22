<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NettoyerJournalActivites extends Command
{
    protected $signature = 'journal:nettoyer';

    protected $description =
        'Supprimer les activités du journal datant de plus de 30 jours';

    public function handle(): int
    {
        $dateLimite = now()->subDays(30);

        $nombreSupprime = DB::table('journal_activites')
            ->where('created_at', '<', $dateLimite)
            ->delete();

        Log::info('Nettoyage du journal des activités', [
            'date_limite' => $dateLimite,
            'nombre_supprime' => $nombreSupprime,
        ]);

        $this->info(
            "{$nombreSupprime} activité(s) supprimée(s)."
        );

        return self::SUCCESS;
    }
}