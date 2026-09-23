<?php

namespace Database\Seeders;

use App\Models\GradeMilitaire;
use Illuminate\Database\Seeder;

class GradeMilitaireSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            [
                'nom_grade' => 'GENERAL',
                'ordre_hierarchique' => 1,
                'categorie' => 'OFFICIER GENERAL',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'COLONEL',
                'ordre_hierarchique' => 2,
                'categorie' => 'OFFICIER SUPPERIEUR',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'COMMANDANT',
                'ordre_hierarchique' => 3,
                'categorie' => 'OFFICIER SUPPERIEUR',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'CAPITAINE',
                'ordre_hierarchique' => 4,
                'categorie' => 'OFFICIER BALTERNE',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'LIEUTENANT',
                'ordre_hierarchique' => 5,
                'categorie' => 'OFFICIER BALTERNE',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'ADJUDANT CHEF',
                'ordre_hierarchique' => 6,
                'categorie' => 'SOUS-OFFICIER SUPPERIEUR',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'ADJUDANT',
                'ordre_hierarchique' => 7,
                'categorie' => 'SOUS-OFFICIER SUPPERIEUR',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'CHEF',
                'ordre_hierarchique' => 8,
                'categorie' => 'SOUS-OFFICIER SUBALTERNE',
                'insigne_symbole' => null,
                'actif' => true,
            ],
        ];

        foreach ($grades as $grade) {
            GradeMilitaire::updateOrCreate(
                [
                    'ordre_hierarchique' => $grade['ordre_hierarchique'],
                ],
                $grade
            );
        }
    }
}