<?php

use App\Http\Controllers\Api\VetController;
use Illuminate\Support\Facades\Route;

// Contrato de ms-vets. Público vía gateway: GET /api/vets[?page=]
Route::get('/vets', [VetController::class, 'index']);
