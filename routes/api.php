<?php

use App\Http\Controllers\DemandeController;
use Illuminate\Support\Facades\Route;

Route::post('/demandes', [DemandeController::class, 'store']);
