<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PetRequest;
use App\Http\Resources\PetResource;
use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;

class PetController extends Controller
{
    /**
     * Ver mascota.
     *
     * Incluye tipo y owner.
     */
    public function show(Pet $pet): PetResource
    {
        return PetResource::make($pet->load(['type', 'owner']));
    }

    /** Registrar mascota. */
    public function store(PetRequest $request, Owner $owner): JsonResponse
    {
        $pet = $owner->pets()->create($request->validated());

        return PetResource::make($pet->load(['type', 'owner']))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Editar mascota.
     *
     * 404 si el pet no pertenece al owner.
     */
    public function update(PetRequest $request, Owner $owner, Pet $pet): PetResource
    {
        $pet->update($request->validated());

        return PetResource::make($pet->load(['type', 'owner']));
    }
}
