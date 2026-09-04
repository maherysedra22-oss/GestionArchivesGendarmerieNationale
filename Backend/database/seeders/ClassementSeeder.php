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
            // COURRIERS ARRIVES
            // =========================
            [
                'nom_class' => 'Administration',
                'type_courrier' => 'ARRIVE',
                'actif' => true,
            ],
            [
                'nom_class' => 'Personnel',
                'type_courrier' => 'ARRIVE',
                'actif' => true,
            ],
            [
                'nom_class' => 'Opérations',
                'type_courrier' => 'ARRIVE',
                'actif' => true,
            ],
            [
                'nom_class' => 'Formation',
                'type_courrier' => 'ARRIVE',
                'actif' => true,
            ],
            [
                'nom_class' => 'Logistique',
                'type_courrier' => 'ARRIVE',
                'actif' => true,
            ],
            [
                'nom_class' => 'Finances',
                'type_courrier' => 'ARRIVE',
                'actif' => true,
            ],
            [
                'nom_class' => 'Archives',
                'type_courrier' => 'ARRIVE',
                'actif' => true,
            ],

            // =========================
            // COURRIERS DEPART
            // =========================
            [
                'nom_class' => 'Administration',
                'type_courrier' => 'DEPART',
                'actif' => true,
            ],
            [
                'nom_class' => 'Personnel',
                'type_courrier' => 'DEPART',
                'actif' => true,
            ],
            [
                'nom_class' => 'Opérations',
                'type_courrier' => 'DEPART',
                'actif' => true,
            ],
            [
                'nom_class' => 'Formation',
                'type_courrier' => 'DEPART',
                'actif' => true,
            ],
            [
                'nom_class' => 'Logistique',
                'type_courrier' => 'DEPART',
                'actif' => true,
            ],
            [
                'nom_class' => 'Finances',
                'type_courrier' => 'DEPART',
                'actif' => true,
            ],
            [
                'nom_class' => 'Archives',
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