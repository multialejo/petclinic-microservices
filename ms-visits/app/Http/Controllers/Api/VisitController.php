<?php

namespace App\Http\Controllers\Api;

use App\Events\VisitCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexVisitsRequest;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Visit;
use App\Services\CustomersClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class VisitController extends Controller
{
    /**
     * CU-03 / CU-08: visitas de varios pets en una sola llamada (?pet_ids=1,2,3).
     * Orden: pet_id y luego fecha ascendente (@OrderBy("date ASC") del As-Is). No consulta a ms-customers.
     */
    public function index(IndexVisitsRequest $request): AnonymousResourceCollection
    {
        return VisitResource::collection(
            Visit::whereIn('pet_id', $request->petIds())
                ->orderBy('pet_id')
                ->orderBy('visit_date')
                ->orderBy('id')
                ->get()
        );
    }

    /**
     * CU-08: agendar visita. Antes de guardar se verifica por HTTP en ms-customers que el pet exista
     * y pertenezca al owner (404 / 422 / 503 controlados, docs/fase2 §5).
     */
    public function store(StoreVisitRequest $request, CustomersClient $customers): JsonResponse
    {
        $data = $request->validated();

        $pet = $customers->findPet((int) $data['pet_id']);

        if ((int) ($pet['owner_id'] ?? 0) !== (int) $data['owner_id']) {
            throw ValidationException::withMessages(['pet_id' => 'El pet no pertenece al owner.']);
        }

        $visit = Visit::create([
            'pet_id' => $data['pet_id'],
            'visit_date' => $data['date'],
            'description' => $data['description'],
        ]);

        $owner = $pet['owner'] ?? [];
        VisitCreated::dispatch(
            $visit,
            (int) $data['owner_id'],
            (string) ($pet['name'] ?? ''),
            trim(($owner['first_name'] ?? '').' '.($owner['last_name'] ?? '')),
        );

        return VisitResource::make($visit)->response()->setStatusCode(201);
    }
}
