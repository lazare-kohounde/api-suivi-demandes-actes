<?php

use App\Http\Controllers\DemandeController;
use Illuminate\Support\Facades\Route;

Route::get('/demandes/stats', [DemandeController::class, 'stats']);
Route::post('/demandes', [DemandeController::class, 'store']);
Route::get('/usagers/{npi}/demandes', [DemandeController::class, 'indexForUsager']);
Route::patch('/demandes/{demande}/statut', [DemandeController::class, 'updateStatut']);
