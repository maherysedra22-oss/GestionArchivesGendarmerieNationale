<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Vérifie si l'utilisateur possède la permission demandée.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $utilisateur = $request->user();

        // Vérification de l'authentification
        if (!$utilisateur) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Vérification du statut de l'utilisateur
        if (!$utilisateur->statut) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est désactivé.',
            ], 403);
        }

        // Vérification de la permission
        if (!$utilisateur->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé.',
                'permission_requise' => $permission,
            ], 403);
        }

        return $next($request);
    }
}