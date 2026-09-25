<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Recherche du rôle Administrateur
        $administrateur = Role::where(
            'nom_role',
            'Administrateur'
        )->first();

        if (!$administrateur) {
            $this->command->error(
                "Le rôle Administrateur n'existe pas."
            );

            return;
        }

        // Administrateur = rôle système
        $administrateur->update([
            'systeme' => true,
            'actif' => true,
        ]);

        // Récupérer les permissions actives
        // en excluant le module Grades
        $permissions = Permission::where('actif', true)
            ->where(
                'code_permission',
                'not like',
                'grades.%'
            )
            ->pluck('id_permission')
            ->toArray();

        // Donner les permissions sélectionnées
        // à Administrateur
        $administrateur->permissions()->sync($permissions);

        $this->command->info(
            'Administrateur possède maintenant les permissions actives hors Grades.'
        );

        $this->command->info(
            count($permissions) .
            ' permissions attribuées à Administrateur.'
        );
    }
}