<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VetResource;
use App\Models\Vet;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VetController extends Controller
{
    /** Número de elementos por página, igual que el monolito original (VetController.listHtml). */
    public const PER_PAGE = 5;

    /**
     * Listar veterinarios.
     *
     * Con especialidades, paginado.
     */
    public function index(): AnonymousResourceCollection
    {
        return VetResource::collection(
            Vet::with('specialties')->orderBy('id')->paginate(self::PER_PAGE)
        );
    }
}
