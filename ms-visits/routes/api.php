<?php

use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

// Contrato de ms-visits. Público vía gateway: /api/visits
Route::get('/visits', [VisitController::class, 'index']);
Route::post('/visits', [VisitController::class, 'store']);
