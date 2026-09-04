<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            [
                'lib_officiel_desti' => 'Commandement de la Gendarmerie Nationale',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Ministère de la Défense Nationale',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Ministère de la Justice',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Ministère de l’Intérieur',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Préfecture',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Tribunal',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Brigade de Gendarmerie',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Groupement de Gendarmerie',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Région de Gendarmerie',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'Autre',
                'actif' => true,
            ],
        ];

        foreach ($destinations as $destination) {
            Destination::updateOrCreate(
                [
                    'lib_officiel_desti' =>
                        $destination['lib_officiel_desti'],
                ],
                $destination
            );
        }
    }
}