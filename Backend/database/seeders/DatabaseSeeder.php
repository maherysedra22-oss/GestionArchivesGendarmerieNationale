<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Permissions
            PermissionSeeder::class,

            // Rôles
            RoleSeeder::class,

            // Attribution de toutes les permissions
            // au rôle Administrateur
            RolePermissionSeeder::class,

            // Données de référence
            GradeMilitaireSeeder::class,
            PieceSuiteSeeder::class,
            NatureCourrierDepartSeeder::class,
            ClassementSeeder::class,
            DestinationSeeder::class,

            // Utilisateur administrateur par défaut
            UtilisateurSeeder::class,
        ]);
    }
}
