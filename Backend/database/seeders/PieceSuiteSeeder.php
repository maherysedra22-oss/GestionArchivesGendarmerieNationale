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
                'nom_piece_suit' => '/3-FION',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => '/3-DSIT',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => '/3-DOE',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => '/3-DQG',
                'actif' => true,
            ],
            [
                'nom_piece_suit' => '/3-COM',
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