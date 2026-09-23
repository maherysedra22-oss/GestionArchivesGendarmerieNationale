<?php

namespace Database\Seeders;

use App\Models\Classement;
use Illuminate\Database\Seeder;

class ClassementSeeder extends Seeder
{
    public function run(): void
    {
        $classements = [

            // =========================
            // COURRIERS DEPART
            // =========================
            [
                'nom_class' => '/2 archive',
                'type_courrier' => 'DEPART',
                'actif' => true,
            ],

        ];

        foreach ($classements as $classement) {
            Classement::updateOrCreate(
                [
                    'nom_class' => $classement['nom_class'],
                    'type_courrier' => $classement['type_courrier'],
                ],
                $classement
            );
        }
    }
}