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
                'nom_nature' => 'Lettre',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Note de service',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Note d’information',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Rapport',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Compte rendu',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Demande',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Réponse',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Convocation',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Invitation',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Transmission',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Ordre de mission',
                'actif' => true,
            ],
            [
                'nom_nature' => 'Bordereau d’envoi',
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