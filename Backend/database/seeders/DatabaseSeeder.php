<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            GradeMilitaireSeeder::class,
            PieceSuiteSeeder::class,
            NatureCourrierDepartSeeder::class,
            ClassementSeeder::class,
            DestinationSeeder::class,
            UtilisateurSeeder::class,
        ]);
    }
}