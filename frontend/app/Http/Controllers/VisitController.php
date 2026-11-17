<?php

namespace App\Http\Controllers;

use App\Exceptions\GatewayUnavailableException;
use App\Services\GatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** CU-08: agendar visita (datos del pet + visitas previas, como el formulario del As-Is). */
class VisitController extends Controller
{
    public function __construct(private GatewayClient $gateway) {}

    public function create(int $ownerId, int $petId): View
    {
        $pet = $this->gateway->get("/api/customers/pets/{$petId}")['data'];
        abort_unless((int) $pet['owner_id'] === $ownerId, 404);

        $visits = [];
        $visitsUnavailable = false;

        try {
            $visits = $this->gateway->get('/api/visits', ['pet_ids' => $petId])['data'] ?? [];
        } catch (GatewayUnavailableException) {
            $visitsUnavailable = true;
        }

        return view('visits.form', [
            'pet' => $pet,
            'visits' => $visits,
            'visitsUnavailable' => $visitsUnavailable,
            'defaultDate' => now()->addDay()->toDateString(), // As-Is: fecha por defecto = mañana
        ]);
    }

    public function store(Request $request, int $ownerId, int $petId): RedirectResponse
    {
        $this->gateway->post('/api/visits', [
            'owner_id' => $ownerId,
            'pet_id' => $petId,
            'date' => $request->input('date'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('owners.show', $ownerId)->with('status', 'La visita fue agendada.');
    }
}
