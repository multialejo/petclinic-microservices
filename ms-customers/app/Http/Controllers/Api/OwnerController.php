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
     * CU-01: buscar owners por prefijo de apellido (sin parámetro: todos). Cada owner con pets[] {id, name}.
     * Las reglas de UI del As-Is (0 resultados, 1 resultado → detalle) las aplica el frontend con `meta.total`.
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

    /** CU-02: registrar owner. */
    public function store(OwnerRequest $request): JsonResponse
    {
        $owner = Owner::create($request->validated());

        return OwnerResource::make($owner->load('pets.type'))
            ->response()
            ->setStatusCode(201);
    }

    /** CU-03 (parte customers): ficha del owner con sus pets (con tipo) ordenados por nombre. */
    public function show(Owner $owner): OwnerResource
    {
        return OwnerResource::make($owner->load('pets.type'));
    }

    /** CU-04: editar owner. */
    public function update(OwnerRequest $request, Owner $owner): OwnerResource
    {
        $owner->update($request->validated());

        return OwnerResource::make($owner->load('pets.type'));
    }
}
