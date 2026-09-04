<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourrierArriveController;
use App\Http\Controllers\Api\CourrierDepartController;
use App\Http\Controllers\Api\CourrierDepartDestinationController;
use App\Http\Controllers\Api\CourrierDepartDocumentController;
use App\Http\Controllers\Api\DocumentNumeriqueController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Utilisateur connecté
    Route::get('/me', [AuthController::class, 'me']);

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource(
        'courriers-arrives',
        CourrierArriveController::class
    );
        Route::apiResource(
        'courriers-depart',
        CourrierDepartController::class
    );

    Route::get(
    '/courriers-depart/{id}/destinations',
    [CourrierDepartDestinationController::class, 'index']
    );

    Route::post(
    '/courriers-depart/{id}/destinations',
    [CourrierDepartDestinationController::class, 'store']
    );

    Route::delete(
    '/courriers-depart/{id}/destinations/{idDesti}',
    [CourrierDepartDestinationController::class, 'destroy']
    );

    Route::get(
    '/courriers-depart/{id}/documents',
    [CourrierDepartDocumentController::class, 'index']
    );

    Route::post(
    '/courriers-depart/{id}/documents',
    [CourrierDepartDocumentController::class, 'store']
    );

    Route::delete(
    '/courriers-depart/{id}/documents/{numDoc}',
    [CourrierDepartDocumentController::class, 'destroy']
    );

    Route::post(
    '/documents/upload',
    [DocumentNumeriqueController::class, 'upload']
    );
    Route::get(
    '/documents',
    [DocumentNumeriqueController::class, 'index']
    );
});