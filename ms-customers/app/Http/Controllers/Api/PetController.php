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
     * CU-06 (carga del formulario) y uso interno de ms-visits para validar el pet antes de crear la visita:
     * {id, name, birth_date, type, owner_id, owner: {id, first_name, last_name}}.
     */
    public function show(Pet $pet): PetResource
    {
        return PetResource::make($pet->load(['type', 'owner']));
    }

    /** CU-05: registrar mascota para un owner. */
    public function store(PetRequest $request, Owner $owner): JsonResponse
    {
        $pet = $owner->pets()->create($request->validated());

        return PetResource::make($pet->load(['type', 'owner']))
            ->response()
            ->setStatusCode(201);
    }

    /** CU-06: editar mascota. El binding con scope garantiza 404 si el pet no es de ese owner. */
    public function update(PetRequest $request, Owner $owner, Pet $pet): PetResource
    {
        $pet->update($request->validated());

        return PetResource::make($pet->load(['type', 'owner']));
    }
}
