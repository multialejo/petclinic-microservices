<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VetResource;
use App\Models\Vet;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VetController extends Controller
{
    /** Número de elementos por página, igual que el As-Is (VetController.listHtml). */
    public const PER_PAGE = 5;

    /**
     * CU-09: listar veterinarios con sus especialidades (paginado).
     * Sustituye a GET /vets y /vets.html del monolito.
     */
    public function index(): AnonymousResourceCollection
    {
        return VetResource::collection(
            Vet::with('specialties')->orderBy('id')->paginate(self::PER_PAGE)
        );
    }
}
