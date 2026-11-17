<?php

namespace App\Http\Controllers;

use App\Exceptions\GatewayUnavailableException;
use App\Services\GatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * CU-01..04 y la ficha del owner (CU-03). Reproduce los comportamientos de UI del As-Is
 * que la API no implementa (docs/fase2 §4, "Comportamientos de UI que asume el frontend").
 */
class OwnerController extends Controller
{
    private const FIELDS = ['first_name', 'last_name', 'address', 'city', 'telephone'];

    public function __construct(private GatewayClient $gateway) {}

    public function find(): View
    {
        return view('owners.find');
    }

    /** CU-01: 0 resultados -> formulario con aviso; 1 -> ficha; página fuera de rango -> página 1. */
    public function index(Request $request): View|RedirectResponse
    {
        $lastName = trim((string) $request->query('last_name', ''));
        $page = max(1, (int) $request->query('page', 1));

        $result = $this->gateway->get('/api/customers/owners', array_filter([
            'last_name' => $lastName,
            'page' => $page,
        ]));

        $total = (int) data_get($result, 'meta.total', 0);

        if ($total === 0) {
            return redirect()->route('owners.find')
                ->withInput(['last_name' => $lastName])
                ->withErrors(['last_name' => 'No se encontró ningún dueño.']);
        }

        if ($total === 1) {
            return redirect()->route('owners.show', data_get($result, 'data.0.id'));
        }

        if (empty($result['data']) && $page > 1) {
            return redirect()->route('owners.index', array_filter(['last_name' => $lastName, 'page' => 1]));
        }

        return view('owners.index', [
            'owners' => $result['data'],
            'meta' => $result['meta'],
            'lastName' => $lastName,
        ]);
    }

    public function create(): View
    {
        return view('owners.form', ['owner' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = $this->gateway->post('/api/customers/owners', $request->only(self::FIELDS));

        return redirect()->route('owners.show', data_get($owner, 'data.id'))
            ->with('status', 'Nuevo dueño registrado.');
    }

    /** CU-03: composición en el frontend: owner (ms-customers) + visitas en lote (ms-visits). */
    public function show(int $ownerId): View
    {
        $owner = $this->gateway->get("/api/customers/owners/{$ownerId}")['data'];
        $petIds = array_column($owner['pets'], 'id');

        $visitsByPet = collect();
        $visitsUnavailable = false;

        if ($petIds !== []) {
            try {
                $visitsByPet = Collection::make(
                    $this->gateway->get('/api/visits', ['pet_ids' => implode(',', $petIds)])['data'] ?? []
                )->groupBy('pet_id');
            } catch (GatewayUnavailableException) {
                // Aislamiento de fallos: la ficha se muestra igual, sin visitas y con un aviso.
                $visitsUnavailable = true;
            }
        }

        return view('owners.show', compact('owner', 'visitsByPet', 'visitsUnavailable'));
    }

    public function edit(int $ownerId): View
    {
        return view('owners.form', [
            'owner' => $this->gateway->get("/api/customers/owners/{$ownerId}")['data'],
        ]);
    }

    public function update(Request $request, int $ownerId): RedirectResponse
    {
        $this->gateway->put("/api/customers/owners/{$ownerId}", $request->only(self::FIELDS));

        return redirect()->route('owners.show', $ownerId)->with('status', 'Datos del dueño actualizados.');
    }
}
