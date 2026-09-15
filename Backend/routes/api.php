<?php

use Illuminate\Support\Facades\Route;

// ============================================================================
// CONTROLLERS
// ============================================================================

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourrierArriveController;
use App\Http\Controllers\Api\CourrierDepartController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CourrierDepartDestinationController;
use App\Http\Controllers\Api\CourrierDepartDocumentController;
use App\Http\Controllers\Api\DocumentCourrierArriveController;
use App\Http\Controllers\Api\DocumentNumeriqueController;
use App\Http\Controllers\Api\PieceSuiteController;
use App\Http\Controllers\Api\NatureCourrierDepartController;
use App\Http\Controllers\Api\ClassementController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UtilisateurController;
use App\Http\Controllers\Api\JournalActiviteController;
// ============================================================================
// ROUTES PUBLIQUES
// ============================================================================

Route::post('/login', [AuthController::class, 'login']);

// ============================================================================
// ROUTES PROTÉGÉES PAR SANCTUM
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

    // ========================================================================
    // AUTHENTIFICATION
    // ========================================================================

    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);


    // ========================================================================
    // DASHBOARD
    // ========================================================================

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->middleware('permission:dashboard.view');


    // ========================================================================
    // COURRIERS ARRIVÉS
    // ========================================================================

    // Statistiques
    Route::get(
        '/courriers-arrives/statistiques',
        [CourrierArriveController::class, 'statistiques']
    )->middleware('permission:courriers_arrives.view');

    // Liste
    Route::get(
        '/courriers-arrives',
        [CourrierArriveController::class, 'index']
    )->middleware('permission:courriers_arrives.view');

    // Création
    Route::post(
        '/courriers-arrives',
        [CourrierArriveController::class, 'store']
    )->middleware('permission:courriers_arrives.create');

    // Détail
    Route::get(
        '/courriers-arrives/{id}',
        [CourrierArriveController::class, 'show']
    )->middleware('permission:courriers_arrives.view');

    // Modification PUT
    Route::put(
        '/courriers-arrives/{id}',
        [CourrierArriveController::class, 'update']
    )->middleware('permission:courriers_arrives.update');

    // Modification PATCH
    Route::patch(
        '/courriers-arrives/{id}',
        [CourrierArriveController::class, 'update']
    )->middleware('permission:courriers_arrives.update');

    // Suppression
    Route::delete(
        '/courriers-arrives/{id}',
        [CourrierArriveController::class, 'destroy']
    )->middleware('permission:courriers_arrives.delete');


    // ========================================================================
    // DOCUMENTS DES COURRIERS ARRIVÉS
    // ========================================================================

    // Liste des documents
    Route::get(
        '/courriers-arrives/{id}/documents',
        [DocumentCourrierArriveController::class, 'index']
    )->middleware('permission:documents.view');

    // Upload document
    Route::post(
        '/courriers-arrives/{id}/documents',
        [DocumentCourrierArriveController::class, 'store']
    )->middleware('permission:courriers_arrives.create');

    // Téléchargement
    Route::get(
        '/courriers-arrives/{id}/documents/{numDoc}/download',
        [DocumentCourrierArriveController::class, 'download']
    )->middleware('permission:documents.download');

    // Suppression document
    Route::delete(
        '/courriers-arrives/{id}/documents/{numDoc}',
        [DocumentCourrierArriveController::class, 'destroy']
    )->middleware('permission:courriers_arrives.delete');


    // ========================================================================
    // COURRIERS DÉPART
    // ========================================================================

    // Liste
    Route::get(
        '/courriers-depart',
        [CourrierDepartController::class, 'index']
    )->middleware('permission:courriers_depart.view');

    // Création
    Route::post(
        '/courriers-depart',
        [CourrierDepartController::class, 'store']
    )->middleware('permission:courriers_depart.create');

    // Détail
    Route::get(
        '/courriers-depart/{id}',
        [CourrierDepartController::class, 'show']
    )->middleware('permission:courriers_depart.view');

    // Modification PUT
    Route::put(
        '/courriers-depart/{id}',
        [CourrierDepartController::class, 'update']
    )->middleware('permission:courriers_depart.update');

    // Modification PATCH
    Route::patch(
        '/courriers-depart/{id}',
        [CourrierDepartController::class, 'update']
    )->middleware('permission:courriers_depart.update');

    // Suppression
    Route::delete(
        '/courriers-depart/{id}',
        [CourrierDepartController::class, 'destroy']
    )->middleware('permission:courriers_depart.delete');


    // ========================================================================
    // DESTINATIONS DES COURRIERS DÉPART
    // ========================================================================

    // Liste des destinations d'un courrier
    Route::get(
        '/courriers-depart/{id}/destinations',
        [CourrierDepartDestinationController::class, 'index']
    )->middleware('permission:courriers_depart.view');

    // Ajouter une destination
    Route::post(
        '/courriers-depart/{id}/destinations',
        [CourrierDepartDestinationController::class, 'store']
    )->middleware('permission:courriers_depart.create');

    // Supprimer une destination
    Route::delete(
        '/courriers-depart/{id}/destinations/{idDesti}',
        [CourrierDepartDestinationController::class, 'destroy']
    )->middleware('permission:courriers_depart.delete');


    // ========================================================================
    // DOCUMENTS DES COURRIERS DÉPART
    // ========================================================================

    // Liste
    Route::get(
        '/courriers-depart/{id}/documents',
        [CourrierDepartDocumentController::class, 'index']
    )->middleware('permission:documents.view');

    // Upload
    Route::post(
        '/courriers-depart/{id}/documents',
        [CourrierDepartDocumentController::class, 'store']
    )->middleware('permission:courriers_depart.create');

    // Téléchargement
    Route::get(
        '/courriers-depart/{id}/documents/{numDoc}/download',
        [CourrierDepartDocumentController::class, 'download']
    )->middleware('permission:documents.download');

    // Suppression
    Route::delete(
        '/courriers-depart/{id}/documents/{numDoc}',
        [CourrierDepartDocumentController::class, 'destroy']
    )->middleware('permission:courriers_depart.delete');


    // ========================================================================
    // DOCUMENTS NUMÉRIQUES
    // ========================================================================

    // Upload général
    Route::post(
        '/documents/upload',
        [DocumentNumeriqueController::class, 'upload']
    )->middleware('permission:documents.view');

    // Liste
    Route::get(
        '/documents',
        [DocumentNumeriqueController::class, 'index']
    )->middleware('permission:documents.view');


    // ============================================================================
    // PIÈCES DE SUITE
    // ============================================================================

    Route::get('/pieces-suite', [PieceSuiteController::class, 'index'])
        ->middleware('permission:courriers_arrives.view');

    Route::post('/pieces-suite', [PieceSuiteController::class, 'store'])
        ->middleware('permission:courriers_arrives.create');

    Route::get('/pieces-suite/{id}', [PieceSuiteController::class, 'show'])
        ->middleware('permission:courriers_arrives.view');

    Route::put('/pieces-suite/{id}', [PieceSuiteController::class, 'update'])
        ->middleware('permission:courriers_arrives.update');

    Route::patch('/pieces-suite/{id}', [PieceSuiteController::class, 'update'])
        ->middleware('permission:courriers_arrives.update');

    Route::delete('/pieces-suite/{id}', [PieceSuiteController::class, 'destroy'])
        ->middleware('permission:courriers_arrives.delete');


    // ============================================================================
    // NATURES COURRIERS DEPART
    // ============================================================================

    Route::get('/natures-courriers-depart', [NatureCourrierDepartController::class, 'index'])
        ->middleware('permission:courriers_depart.view');

    Route::post('/natures-courriers-depart', [NatureCourrierDepartController::class, 'store'])
        ->middleware('permission:courriers_depart.create');

    Route::get('/natures-courriers-depart/{id}', [NatureCourrierDepartController::class, 'show'])
        ->middleware('permission:courriers_depart.view');

    Route::put('/natures-courriers-depart/{id}', [NatureCourrierDepartController::class, 'update'])
        ->middleware('permission:courriers_depart.update');

    Route::patch('/natures-courriers-depart/{id}', [NatureCourrierDepartController::class, 'update'])
        ->middleware('permission:courriers_depart.update');

    Route::delete('/natures-courriers-depart/{id}', [NatureCourrierDepartController::class, 'destroy'])
        ->middleware('permission:courriers_depart.delete');


    // ============================================================================
    // CLASSEMENTS
    // ============================================================================

    Route::get('/classements', [ClassementController::class, 'index'])
        ->middleware('permission:courriers_depart.view');

    Route::post('/classements', [ClassementController::class, 'store'])
        ->middleware('permission:courriers_depart.create');

    Route::get('/classements/{id}', [ClassementController::class, 'show'])
        ->middleware('permission:courriers_depart.view');

    Route::put('/classements/{id}', [ClassementController::class, 'update'])
        ->middleware('permission:courriers_depart.update');

    Route::patch('/classements/{id}', [ClassementController::class, 'update'])
        ->middleware('permission:courriers_depart.update');

    Route::delete('/classements/{id}', [ClassementController::class, 'destroy'])
        ->middleware('permission:courriers_depart.delete');


    // ============================================================================
    // DESTINATIONS
    // ============================================================================

    Route::get('/destinations', [DestinationController::class, 'index'])
        ->middleware('permission:courriers_depart.view');

    Route::post('/destinations', [DestinationController::class, 'store'])
        ->middleware('permission:courriers_depart.create');

    Route::get('/destinations/{id}', [DestinationController::class, 'show'])
        ->middleware('permission:courriers_depart.view');

    Route::put('/destinations/{id}', [DestinationController::class, 'update'])
        ->middleware('permission:courriers_depart.update');

    Route::patch('/destinations/{id}', [DestinationController::class, 'update'])
        ->middleware('permission:courriers_depart.update');

    Route::delete('/destinations/{id}', [DestinationController::class, 'destroy'])
        ->middleware('permission:courriers_depart.delete');

// ============================================================================
// GESTION DES RÔLES
// ============================================================================

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create');

    Route::get('/roles/{id}', [RoleController::class, 'show'])
        ->middleware('permission:roles.view');

    Route::put('/roles/{id}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update');

    Route::patch('/roles/{id}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update');

    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete');

    Route::get('/permissions', [RoleController::class, 'permissions'])
        ->middleware('permission:roles.view');

    Route::get('/roles/{id}/permissions', [RoleController::class, 'rolePermissions'])
        ->middleware('permission:roles.view');

    Route::put('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])
        ->middleware('permission:roles.update');

    // ============================================================================
    // GESTION DES UTILISATEURS
    // ============================================================================

    Route::get('/utilisateurs', [UtilisateurController::class, 'index'])
        ->middleware('permission:utilisateurs.view');

    Route::post('/utilisateurs', [UtilisateurController::class, 'store'])
        ->middleware('permission:utilisateurs.create');

    Route::get('/utilisateurs/{id}', [UtilisateurController::class, 'show'])
        ->middleware('permission:utilisateurs.view');

    Route::put('/utilisateurs/{id}', [UtilisateurController::class, 'update'])
        ->middleware('permission:utilisateurs.update');

    Route::patch('/utilisateurs/{id}', [UtilisateurController::class, 'update'])
        ->middleware('permission:utilisateurs.update');

    Route::delete('/utilisateurs/{id}', [UtilisateurController::class, 'destroy'])
        ->middleware('permission:utilisateurs.delete');

    Route::patch(
        '/utilisateurs/{id}/statut',
        [UtilisateurController::class, 'updateStatut']
    )->middleware('permission:utilisateurs.update');

    Route::patch(
        '/utilisateurs/{id}/reset-password',
        [UtilisateurController::class, 'resetPassword']
    )->middleware('permission:utilisateurs.update');


    // ============================================================================
    // DONNÉES POUR LES FORMULAIRES UTILISATEURS
    // ============================================================================

    Route::get(
        '/utilisateurs-references/roles',
        [UtilisateurController::class, 'roles']
    )->middleware('permission:utilisateurs.view');

    Route::get(
        '/utilisateurs-references/grades',
        [UtilisateurController::class, 'grades']
    )->middleware('permission:utilisateurs.view');


    // ============================================================================
    // JOURNAL DES ACTIVITÉS
    // ============================================================================

    Route::get(
        '/journal-activites',
        [JournalActiviteController::class, 'index']
    )->middleware('permission:journal.view');

    Route::get(
        '/journal-activites/{id}',
        [JournalActiviteController::class, 'show']
    )->middleware('permission:journal.view');

});