<?php

use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

// Contrato: docs/fase2/README.md §4 (ms-visits). Público vía gateway: /api/visits
Route::get('/visits', [VisitController::class, 'index']);
Route::post('/visits', [VisitController::class, 'store']);
