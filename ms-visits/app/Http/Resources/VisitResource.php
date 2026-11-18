<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * La columna se llama visit_date (paridad con el monolito original); el contrato JSON expone `date`.
 *
 * @mixin \App\Models\Visit
 */
class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pet_id' => $this->pet_id,
            'date' => $this->visit_date?->toDateString(),
            'description' => $this->description,
        ];
    }
}
