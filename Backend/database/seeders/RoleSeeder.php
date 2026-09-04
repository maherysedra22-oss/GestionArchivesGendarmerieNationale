<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nom_role' => 'Administrateur',
                'description' => 'Gestion complète de l’application',
                'actif' => true,
            ],
            [
                'nom_role' => 'Archiviste',
                'description' => 'Gestion des courriers, documents et archives',
                'actif' => true,
            ],
            [
                'nom_role' => 'Lecture',
                'description' => 'Consultation des courriers et documents',
                'actif' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['nom_role' => $role['nom_role']],
                $role
            );
        }
    }
}