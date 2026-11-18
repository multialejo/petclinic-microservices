<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PetTypeResource;
use App\Models\PetType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PetTypeController extends Controller
{
    /** Listar tipos de mascota. */
    public function index(): AnonymousResourceCollection
    {
        return PetTypeResource::collection(PetType::orderBy('name')->get());
    }
}
