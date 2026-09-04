<?php

namespace Database\Seeders;

use App\Models\PieceSuite;
use Illuminate\Database\Seeder;

class PieceSuiteSeeder extends Seeder
{
    public function run(): void
    {
        $pieces = [
            [
                'nom_piece_suit' => 'Aucune',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => 'Réponse',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => 'Accusé de réception',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => 'Note de service',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => 'Compte rendu',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => 'Rapport',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => 'Demande de complément',
                'actif' => true,
            ],
        ];

        foreach ($pieces as $piece) {
            PieceSuite::updateOrCreate(
                [
                    'nom_piece_suit' => $piece['nom_piece_suit'],
                ],
                $piece
            );
        }
    }
}