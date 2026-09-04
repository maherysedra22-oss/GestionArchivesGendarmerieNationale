<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        Utilisateur::create([
            'matricule' => 'ADM001',
            'nom' => 'ADMIN',
            'prenom' => 'Administrateur',
            'poste_fonction' => 'Administrateur du système',
            'email' => 'admin@gendarmerie.mg',
            'mot_de_passe' => Hash::make('Admin@123456'),
            'id_grade' => 1,
            'id_role' => 1,
            'statut' => true,
            'derniere_connexion' => null,
            'doit_changer_mdp' => true,
        ]);
    }
}