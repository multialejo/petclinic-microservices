<?php

namespace App\Http\Controllers;

use App\Services\GatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** CU-09: veterinarios y especialidades (paginado de 5, como /vets.html del As-Is). */
class VetController extends Controller
{
    public function __construct(private GatewayClient $gateway) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $result = $this->gateway->get('/api/vets', ['page' => $page]);

        if (empty($result['data']) && $page > 1) {
            return redirect('/vets.html');
        }

        return view('vets.index', ['vets' => $result['data'], 'meta' => $result['meta']]);
    }
}
