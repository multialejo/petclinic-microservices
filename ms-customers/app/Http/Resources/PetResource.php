<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Pet. Los campos dependen de lo cargado: en el listado de owners solo {id, name};
 * en la ficha del owner con `type`; en GET /api/pets/{id} además `owner` (docs/fase2 §4).
 *
 * @mixin \App\Models\Pet
 */
class PetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->whenHas('birth_date', fn ($date) => $date?->toDateString()),
            'type' => new PetTypeResource($this->whenLoaded('type')),
            'owner_id' => $this->whenHas('owner_id'),
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner->id,
                'first_name' => $this->owner->first_name,
                'last_name' => $this->owner->last_name,
            ]),
        ];
    }
}
