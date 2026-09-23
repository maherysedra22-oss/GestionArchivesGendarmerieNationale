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
                'lib_officiel_desti' => 'DSIT/SEDI',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'DSIT',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'COM/DQG/SAG',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'MDG/CAB',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'SG/DRH',
                'actif' => true,
            ],
            [
                'lib_officiel_desti' => 'DSR',
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