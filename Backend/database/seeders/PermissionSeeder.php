<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'dashboard.view',
                'nom_permission' => 'Voir le tableau de bord',
                'page' => 'dashboard',
                'action' => 'view',
                'description' => 'Permet d’accéder au tableau de bord.',
                'actif' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Courriers arrivés
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'courriers_arrives.view',
                'nom_permission' => 'Voir les courriers arrivés',
                'page' => 'courriers_arrives',
                'action' => 'view',
                'description' => 'Permet de consulter les courriers arrivés.',
                'actif' => true,
            ],

            [
                'code_permission' => 'courriers_arrives.create',
                'nom_permission' => 'Ajouter un courrier arrivé',
                'page' => 'courriers_arrives',
                'action' => 'create',
                'description' => 'Permet d’ajouter un courrier arrivé.',
                'actif' => true,
            ],

            [
                'code_permission' => 'courriers_arrives.update',
                'nom_permission' => 'Modifier un courrier arrivé',
                'page' => 'courriers_arrives',
                'action' => 'update',
                'description' => 'Permet de modifier un courrier arrivé.',
                'actif' => true,
            ],

            [
                'code_permission' => 'courriers_arrives.delete',
                'nom_permission' => 'Supprimer un courrier arrivé',
                'page' => 'courriers_arrives',
                'action' => 'delete',
                'description' => 'Permet de supprimer un courrier arrivé.',
                'actif' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Courriers départ
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'courriers_depart.view',
                'nom_permission' => 'Voir les courriers départ',
                'page' => 'courriers_depart',
                'action' => 'view',
                'description' => 'Permet de consulter les courriers départ.',
                'actif' => true,
            ],

            [
                'code_permission' => 'courriers_depart.create',
                'nom_permission' => 'Ajouter un courrier départ',
                'page' => 'courriers_depart',
                'action' => 'create',
                'description' => 'Permet d’ajouter un courrier départ.',
                'actif' => true,
            ],

            [
                'code_permission' => 'courriers_depart.update',
                'nom_permission' => 'Modifier un courrier départ',
                'page' => 'courriers_depart',
                'action' => 'update',
                'description' => 'Permet de modifier un courrier départ.',
                'actif' => true,
            ],

            [
                'code_permission' => 'courriers_depart.delete',
                'nom_permission' => 'Supprimer un courrier départ',
                'page' => 'courriers_depart',
                'action' => 'delete',
                'description' => 'Permet de supprimer un courrier départ.',
                'actif' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'documents.view',
                'nom_permission' => 'Voir les documents',
                'page' => 'documents',
                'action' => 'view',
                'description' => 'Permet de consulter les documents numériques.',
                'actif' => true,
            ],

            [
                'code_permission' => 'documents.download',
                'nom_permission' => 'Télécharger les documents',
                'page' => 'documents',
                'action' => 'download',
                'description' => 'Permet de télécharger les documents numériques.',
                'actif' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Utilisateurs
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'utilisateurs.view',
                'nom_permission' => 'Voir les utilisateurs',
                'page' => 'utilisateurs',
                'action' => 'view',
                'description' => 'Permet de consulter les utilisateurs.',
                'actif' => true,
            ],

            [
                'code_permission' => 'utilisateurs.create',
                'nom_permission' => 'Ajouter un utilisateur',
                'page' => 'utilisateurs',
                'action' => 'create',
                'description' => 'Permet de créer un utilisateur.',
                'actif' => true,
            ],

            [
                'code_permission' => 'utilisateurs.update',
                'nom_permission' => 'Modifier un utilisateur',
                'page' => 'utilisateurs',
                'action' => 'update',
                'description' => 'Permet de modifier un utilisateur.',
                'actif' => true,
            ],

            [
                'code_permission' => 'utilisateurs.delete',
                'nom_permission' => 'Supprimer un utilisateur',
                'page' => 'utilisateurs',
                'action' => 'delete',
                'description' => 'Permet de supprimer un utilisateur.',
                'actif' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Rôles
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'roles.view',
                'nom_permission' => 'Voir les rôles',
                'page' => 'roles',
                'action' => 'view',
                'description' => 'Permet de consulter les rôles.',
                'actif' => true,
            ],

            [
                'code_permission' => 'roles.create',
                'nom_permission' => 'Ajouter un rôle',
                'page' => 'roles',
                'action' => 'create',
                'description' => 'Permet de créer un rôle.',
                'actif' => true,
            ],

            [
                'code_permission' => 'roles.update',
                'nom_permission' => 'Modifier un rôle',
                'page' => 'roles',
                'action' => 'update',
                'description' => 'Permet de modifier un rôle.',
                'actif' => true,
            ],

            [
                'code_permission' => 'roles.delete',
                'nom_permission' => 'Supprimer un rôle',
                'page' => 'roles',
                'action' => 'delete',
                'description' => 'Permet de supprimer un rôle.',
                'actif' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Grades
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'grades.view',
                'nom_permission' => 'Voir les grades',
                'page' => 'grades',
                'action' => 'view',
                'description' => 'Permet de consulter les grades militaires.',
                'actif' => true,
            ],

            [
                'code_permission' => 'grades.create',
                'nom_permission' => 'Ajouter un grade',
                'page' => 'grades',
                'action' => 'create',
                'description' => 'Permet de créer un grade militaire.',
                'actif' => true,
            ],

            [
                'code_permission' => 'grades.update',
                'nom_permission' => 'Modifier un grade',
                'page' => 'grades',
                'action' => 'update',
                'description' => 'Permet de modifier un grade militaire.',
                'actif' => true,
            ],

            [
                'code_permission' => 'grades.delete',
                'nom_permission' => 'Supprimer un grade',
                'page' => 'grades',
                'action' => 'delete',
                'description' => 'Permet de supprimer un grade militaire.',
                'actif' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Journal des activités
            |--------------------------------------------------------------------------
            */
            [
                'code_permission' => 'journal.view',
                'nom_permission' => 'Voir le journal des activités',
                'page' => 'journal',
                'action' => 'view',
                'description' => 'Permet de consulter le journal des activités.',
                'actif' => true,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'code_permission' => $permission['code_permission'],
                ],
                $permission
            );
        }
    }
}