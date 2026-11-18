<?php

namespace App\Http\Controllers;

use App\Exceptions\GatewayUnavailableException;
use App\Services\GatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * CU-01..04 y la ficha del owner (CU-03). Mismas URLs, campos de formulario y mensajes que el
 * OwnerController del monolito; los datos salen del API Gateway (docs/fase2 §4).
 */
class OwnerController extends Controller
{
    /** Campo del formulario (monolito) => campo de la API. */
    private const FIELDS = [
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'address' => 'address',
        'city' => 'city',
        'telephone' => 'telephone',
    ];

    public function __construct(private GatewayClient $gateway) {}

    public function find(): View
    {
        return view('owners.find');
    }

    /** CU-01: 0 resultados -> formulario con "has not been found"; 1 -> ficha; página fuera de rango -> página 1. */
    public function index(Request $request): View|RedirectResponse
    {
        $lastName = trim((string) $request->query('lastName', ''));
        $page = max(1, (int) $request->query('page', 1));

        $result = $this->gateway->get('/api/customers/owners', array_filter([
            'last_name' => $lastName,
            'page' => $page,
        ]));

        $total = (int) data_get($result, 'meta.total', 0);

        if ($total === 0) {
            return redirect('/owners/find')
                ->withInput(['lastName' => $lastName])
                ->withErrors(['lastName' => 'has not been found']);
        }

        if ($total === 1) {
            return redirect('/owners/'.data_get($result, 'data.0.id'));
        }

        if (empty($result['data']) && $page > 1) {
            return redirect('/owners?'.http_build_query(array_filter(['lastName' => $lastName, 'page' => 1])));
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
        $owner = $this->gateway->post('/api/customers/owners', $this->payload($request), array_flip(self::FIELDS));

        return redirect('/owners/'.data_get($owner, 'data.id'))->with('message', 'New Owner Created');
    }

    /** CU-03: composición en el frontend: owner (ms-customers) + visitas en lote (ms-visits). */
    public function show(int $ownerId): View
    {
        $owner = $this->gateway->get("/api/customers/owners/{$ownerId}")['data'];
        $petIds = array_column($owner['pets'], 'id');

        $visitsByPet = collect();
        $error = null;

        if ($petIds !== []) {
            try {
                $visitsByPet = Collection::make(
                    $this->gateway->get('/api/visits', ['pet_ids' => implode(',', $petIds)])['data'] ?? []
                )->groupBy('pet_id');
            } catch (GatewayUnavailableException) {
                // Aislamiento de fallos: la ficha se muestra igual, sin visitas, con el aviso en el
                // espacio de error que ya tenía la plantilla del monolito (#error-message).
                $error = 'The visits service is unavailable. Visits cannot be shown right now.';
            }
        }

        return view('owners.show', compact('owner', 'visitsByPet', 'error'));
    }

    public function edit(int $ownerId): View
    {
        return view('owners.form', [
            'owner' => $this->gateway->get("/api/customers/owners/{$ownerId}")['data'],
        ]);
    }

    public function update(Request $request, int $ownerId): RedirectResponse
    {
        $this->gateway->put("/api/customers/owners/{$ownerId}", $this->payload($request), array_flip(self::FIELDS));

        return redirect("/owners/{$ownerId}")->with('message', 'Owner Values Updated');
    }

    private function payload(Request $request): array
    {
        $data = [];
        foreach (self::FIELDS as $form => $api) {
            $data[$api] = $request->input($form);
        }

        return $data;
    }
}
