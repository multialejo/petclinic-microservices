<?php

use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\VetController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

// Mismas URLs que el monolito (docs/fase1/endpoints.md) para que la migración sea transparente al usuario.
Route::view('/', 'welcome')->name('home');

Route::get('/owners/find', [OwnerController::class, 'find'])->name('owners.find');
Route::get('/owners', [OwnerController::class, 'index'])->name('owners.index');
Route::get('/owners/new', [OwnerController::class, 'create'])->name('owners.create');
Route::post('/owners/new', [OwnerController::class, 'store'])->name('owners.store');

Route::pattern('ownerId', '[0-9]+');
Route::pattern('petId', '[0-9]+');

Route::get('/owners/{ownerId}', [OwnerController::class, 'show'])->name('owners.show');
Route::get('/owners/{ownerId}/edit', [OwnerController::class, 'edit'])->name('owners.edit');
Route::post('/owners/{ownerId}/edit', [OwnerController::class, 'update'])->name('owners.update');

Route::get('/owners/{ownerId}/pets/new', [PetController::class, 'create'])->name('pets.create');
Route::post('/owners/{ownerId}/pets/new', [PetController::class, 'store'])->name('pets.store');
Route::get('/owners/{ownerId}/pets/{petId}/edit', [PetController::class, 'edit'])->name('pets.edit');
Route::post('/owners/{ownerId}/pets/{petId}/edit', [PetController::class, 'update'])->name('pets.update');

Route::get('/owners/{ownerId}/pets/{petId}/visits/new', [VisitController::class, 'create'])->name('visits.create');
Route::post('/owners/{ownerId}/pets/{petId}/visits/new', [VisitController::class, 'store'])->name('visits.store');

Route::get('/vets.html', [VetController::class, 'index'])->name('vets.index');

// Igual que CrashController del monolito: demuestra la página de error.
Route::get('/oups', function () {
    throw new RuntimeException('Expected: controller used to showcase what happens when an exception is thrown');
})->name('oups');
Route::redirect('/vets', '/vets.html');
