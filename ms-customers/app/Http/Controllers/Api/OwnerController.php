<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerRequest;
use App\Http\Resources\OwnerResource;
use App\Models\Owner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OwnerController extends Controller
{
    /** Número de elementos por página, igual que el As-Is (OwnerController.processFindForm). */
    public const PER_PAGE = 5;

    /**
     * Buscar owners.
     *
     * Filtra por prefijo de `last_name`; sin parámetro devuelve todos. Paginado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['last_name' => ['nullable', 'string', 'max:30']]);

        $lastName = (string) $request->query('last_name', '');

        $owners = Owner::query()
            ->when($lastName !== '', fn ($q) => $q->where(
                'last_name', 'like', addcslashes($lastName, '%_\\').'%'
            ))
            ->with(['pets' => fn ($q) => $q->select('id', 'name', 'owner_id')])
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return OwnerResource::collection($owners);
    }

    /** Registrar owner. */
    public function store(OwnerRequest $request): JsonResponse
    {
        $owner = Owner::create($request->validated());

        return OwnerResource::make($owner->load('pets.type'))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Ver owner.
     *
     * Incluye sus pets con tipo.
     */
    public function show(Owner $owner): OwnerResource
    {
        return OwnerResource::make($owner->load('pets.type'));
    }

    /** Editar owner. */
    public function update(OwnerRequest $request, Owner $owner): OwnerResource
    {
        $owner->update($request->validated());

        return OwnerResource::make($owner->load('pets.type'));
    }
}
