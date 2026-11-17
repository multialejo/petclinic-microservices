<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PetTypeResource;
use App\Models\PetType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PetTypeController extends Controller
{
    /** CU-07: tipos de mascota ordenados por nombre (apoyo a CU-05/06). */
    public function index(): AnonymousResourceCollection
    {
        return PetTypeResource::collection(PetType::orderBy('name')->get());
    }
}
