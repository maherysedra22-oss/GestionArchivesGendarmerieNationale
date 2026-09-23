<?php

namespace Database\Seeders;

use App\Models\NatureCourrierDepart;
use Illuminate\Database\Seeder;

class NatureCourrierDepartSeeder extends Seeder
{
    public function run(): void
    {
        $natures = [
            [
                'nom_nature' => 'Message Porte',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Message Radio',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Pieces',
                'actif' => true,
            ],

            [
                'nom_nature' => '12 Colonnes',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Designation Permanance',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Bilan',
                'actif' => true,
            ],
          
        ];

        foreach ($natures as $nature) {
            NatureCourrierDepart::updateOrCreate(
                [
                    'nom_nature' => $nature['nom_nature'],
                ],
                $nature
            );
        }
    }
}