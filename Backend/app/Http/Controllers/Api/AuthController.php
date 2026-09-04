<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Connexion de l'utilisateur
     */
    public function login(Request $request)
    {
        // Validation des données reçues
        $request->validate([
            'email' => ['required', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ]);

        // Recherche de l'utilisateur par email
        $utilisateur = Utilisateur::where(
            'email',
            $request->email
        )->first();

        // Vérification email + mot de passe
        if (
            !$utilisateur ||
            !Hash::check(
                $request->mot_de_passe,
                $utilisateur->mot_de_passe
            )
        ) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.'
            ], 401);
        }

        // Vérification du statut du compte
        if (!$utilisateur->statut) {
            return response()->json([
                'message' => 'Votre compte est désactivé.'
            ], 403);
        }

        // Suppression des anciens tokens
        $utilisateur->tokens()->delete();

        // Création d'un nouveau token
        $token = $utilisateur->createToken(
            'auth-token'
        )->plainTextToken;

        // Mise à jour de la dernière connexion
        $utilisateur->derniere_connexion = now();

        // Après connexion, le changement de mot de passe
        // sera géré séparément si doit_changer_mdp = true
        $utilisateur->save();

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'token_type' => 'Bearer',

            'doit_changer_mdp' =>
                $utilisateur->doit_changer_mdp,

            'utilisateur' => [
                'id_utilisateur' => $utilisateur->id_utilisateur,
                'matricule' => $utilisateur->matricule,
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'poste_fonction' => $utilisateur->poste_fonction,
                'email' => $utilisateur->email,
                'statut' => $utilisateur->statut,

                'role' => $utilisateur->role
                    ? $utilisateur->role->nom_role
                    : null,

                'grade' => $utilisateur->grade
                    ? $utilisateur->grade->nom_grade
                    : null,
            ],
        ], 200);
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout(Request $request)
    {
    $user = $request->user();

    if ($user) {
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }
    }

    return response()->json([
        'message' => 'Déconnexion réussie.'
    ], 200);
    }


    /**
     * Informations de l'utilisateur connecté
     */
    public function me(Request $request)
    {
        $utilisateur = $request->user();

        return response()->json([
            'utilisateur' => [
                'id_utilisateur' => $utilisateur->id_utilisateur,
                'matricule' => $utilisateur->matricule,
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'poste_fonction' => $utilisateur->poste_fonction,
                'email' => $utilisateur->email,
                'statut' => $utilisateur->statut,
                'doit_changer_mdp' => $utilisateur->doit_changer_mdp,

                'role' => $utilisateur->role
                    ? $utilisateur->role->nom_role
                    : null,

                'grade' => $utilisateur->grade
                    ? $utilisateur->grade->nom_grade
                    : null,
            ],
        ], 200);
    }
}