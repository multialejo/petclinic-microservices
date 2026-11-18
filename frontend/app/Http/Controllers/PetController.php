<?php

namespace App\Http\Controllers;

use App\Services\GatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Registrar y editar mascotas; tipos para el select. */
class PetController extends Controller
{
    /** Campo del formulario (monolito) => campo de la API. */
    private const FIELDS = ['name' => 'name', 'birthDate' => 'birth_date', 'type' => 'type_id'];

    public function __construct(private GatewayClient $gateway) {}

    public function create(int $ownerId): View
    {
        return view('pets.form', [
            'owner' => $this->gateway->get("/api/customers/owners/{$ownerId}")['data'],
            'pet' => null,
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request, int $ownerId): RedirectResponse
    {
        $this->gateway->post("/api/customers/owners/{$ownerId}/pets", $this->payload($request), array_flip(self::FIELDS));

        return redirect("/owners/{$ownerId}")->with('message', 'New Pet has been Added');
    }

    public function edit(int $ownerId, int $petId): View
    {
        $pet = $this->gateway->get("/api/customers/pets/{$petId}")['data'];
        abort_unless((int) $pet['owner_id'] === $ownerId, 404);

        return view('pets.form', [
            'owner' => $pet['owner'],
            'pet' => $pet,
            'types' => $this->types(),
        ]);
    }

    public function update(Request $request, int $ownerId, int $petId): RedirectResponse
    {
        $this->gateway->put("/api/customers/owners/{$ownerId}/pets/{$petId}", $this->payload($request), array_flip(self::FIELDS));

        return redirect("/owners/{$ownerId}")->with('message', 'Pet details has been edited');
    }

    /** Ordenados por nombre, como el select del monolito (PetTypeFormatter / findPetTypes). */
    private function types(): array
    {
        return $this->gateway->get('/api/customers/types')['data'];
    }

    private function payload(Request $request): array
    {
        $data = [];
        foreach (self::FIELDS as $form => $api) {
            $data[$api] = $request->input($form);
        }

        // El select envía el nombre del tipo (igual que el monolito); la API espera type_id.
        // Un nombre desconocido se envía tal cual y la API responde 422 "is required" en `type`.
        $type = collect($this->types())->firstWhere('name', $data['type_id']);
        $data['type_id'] = $type['id'] ?? $data['type_id'];

        return $data;
    }
}
