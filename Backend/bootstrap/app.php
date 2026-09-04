<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    $middleware->redirectGuestsTo(function ($request) {
        if ($request->is('api/*')) {
            return null;
        }

        return '/login';
    });

    })

    ->withExceptions(function (Exceptions $exceptions): void {

    // Toutes les routes API doivent retourner du JSON
    $exceptions->shouldRenderJsonWhen(
        fn (Request $request) =>
            $request->is('api/*') || $request->expectsJson(),
    );

    // Erreur de validation : HTTP 422
    $exceptions->render(function (
        \Illuminate\Validation\ValidationException $e,
        Request $request
    ) {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Les données fournies sont invalides.',
                'errors' => $e->errors(),
            ], 422);
        }
    });

    // Ressource introuvable avec findOrFail()
    $exceptions->render(function (
        NotFoundHttpException $e,
        Request $request
    ) {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Ressource introuvable.',
            ], 404);
        }
    });

    // Utilisateur non authentifié
    $exceptions->render(function (
        \Illuminate\Auth\AuthenticationException $e,
        Request $request
    ) {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }
    });


    })->create();
