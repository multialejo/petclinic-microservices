<?php

use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PetTypeController;
use Illuminate\Support\Facades\Route;

// Contrato de ms-customers. Público vía gateway como /api/customers/{resto}.
Route::get('/owners', [OwnerController::class, 'index']);
Route::post('/owners', [OwnerController::class, 'store']);
Route::get('/owners/{owner}', [OwnerController::class, 'show'])->whereNumber('owner');
Route::put('/owners/{owner}', [OwnerController::class, 'update'])->whereNumber('owner');

Route::post('/owners/{owner}/pets', [PetController::class, 'store'])->whereNumber('owner');
Route::put('/owners/{owner}/pets/{pet}', [PetController::class, 'update'])
    ->whereNumber(['owner', 'pet'])
    ->scopeBindings();

// También lo consume ms-visits (servicio -> servicio) para validar pet y owner.
Route::get('/pets/{pet}', [PetController::class, 'show'])->whereNumber('pet');

Route::get('/types', [PetTypeController::class, 'index']);
