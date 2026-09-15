<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Recherche du rôle Administrateur
        |--------------------------------------------------------------------------
        */

        $administrateur = Role::where('nom_role', 'Administrateur')
            ->first();

        if (!$administrateur) {
            $this->command->error(
                'Le rôle Administrateur n\'existe pas dans la base de données.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Administrateur = rôle système
        |--------------------------------------------------------------------------
        */

        $administrateur->update([
            'systeme' => true,
            'actif' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3. Récupération de toutes les permissions actives
        |--------------------------------------------------------------------------
        */

        $permissions = Permission::where('actif', true)
            ->pluck('id_permission')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 4. Donner toutes les permissions à Administrateur
        |--------------------------------------------------------------------------
        */

        $administrateur->permissions()->sync($permissions);

        /*
        |--------------------------------------------------------------------------
        | 5. Message de confirmation
        |--------------------------------------------------------------------------
        */

        $this->command->info(
            'Le rôle Administrateur est maintenant un rôle système.'
        );

        $this->command->info(
            count($permissions) . ' permissions ont été attribuées.'
        );
    }
}