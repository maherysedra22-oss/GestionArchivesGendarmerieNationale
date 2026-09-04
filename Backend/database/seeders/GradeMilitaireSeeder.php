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
                'nom_grade' => 'Gendarme',
                'ordre_hierarchique' => 1,
                'categorie' => 'Militaire du rang',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Gendarme principal',
                'ordre_hierarchique' => 2,
                'categorie' => 'Militaire du rang',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Brigadier',
                'ordre_hierarchique' => 3,
                'categorie' => 'Sous-officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Brigadier-chef',
                'ordre_hierarchique' => 4,
                'categorie' => 'Sous-officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Maréchal des logis',
                'ordre_hierarchique' => 5,
                'categorie' => 'Sous-officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Maréchal des logis-chef',
                'ordre_hierarchique' => 6,
                'categorie' => 'Sous-officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Adjudant',
                'ordre_hierarchique' => 7,
                'categorie' => 'Sous-officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Adjudant-chef',
                'ordre_hierarchique' => 8,
                'categorie' => 'Sous-officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Major',
                'ordre_hierarchique' => 9,
                'categorie' => 'Sous-officier supérieur',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Sous-lieutenant',
                'ordre_hierarchique' => 10,
                'categorie' => 'Officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Lieutenant',
                'ordre_hierarchique' => 11,
                'categorie' => 'Officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Capitaine',
                'ordre_hierarchique' => 12,
                'categorie' => 'Officier',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Chef d’escadron',
                'ordre_hierarchique' => 13,
                'categorie' => 'Officier supérieur',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Lieutenant-colonel',
                'ordre_hierarchique' => 14,
                'categorie' => 'Officier supérieur',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Colonel',
                'ordre_hierarchique' => 15,
                'categorie' => 'Officier supérieur',
                'insigne_symbole' => null,
                'actif' => true,
            ],
            [
                'nom_grade' => 'Général',
                'ordre_hierarchique' => 16,
                'categorie' => 'Officier général',
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