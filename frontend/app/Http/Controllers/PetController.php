<?php

namespace App\Http\Controllers;

use App\Services\GatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** CU-05 (registrar mascota), CU-06 (editar mascota) y CU-07 (tipos, para el select). */
class PetController extends Controller
{
    private const FIELDS = ['name', 'birth_date', 'type_id'];

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
        $this->gateway->post("/api/customers/owners/{$ownerId}/pets", $request->only(self::FIELDS));

        return redirect()->route('owners.show', $ownerId)->with('status', 'Nueva mascota registrada.');
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
        $this->gateway->put("/api/customers/owners/{$ownerId}/pets/{$petId}", $request->only(self::FIELDS));

        return redirect()->route('owners.show', $ownerId)->with('status', 'Mascota actualizada.');
    }

    private function types(): array
    {
        return $this->gateway->get('/api/customers/types')['data'];
    }
}
