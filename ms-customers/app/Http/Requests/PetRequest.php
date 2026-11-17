<?php

namespace App\Http\Requests;

use App\Models\Owner;
use App\Models\Pet;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Reglas de Pet.java + PetValidator + PetController (docs/fase1/er-diagram.md).
 * El nombre es único por owner sin distinguir mayúsculas (equalsIgnoreCase en el As-Is);
 * se compara con LOWER() para que funcione igual en MySQL y en SQLite (tests).
 */
class PetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Owner $owner */
        $owner = $this->route('owner');
        /** @var Pet|null $pet */
        $pet = $this->route('pet');

        return [
            'name' => [
                'required', 'string', 'max:30',
                function (string $attribute, mixed $value, Closure $fail) use ($owner, $pet) {
                    $exists = Pet::query()
                        ->where('owner_id', $owner->id)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) $value)])
                        ->when($pet, fn ($q) => $q->whereKeyNot($pet->id))
                        ->exists();

                    if ($exists) {
                        $fail('El owner ya tiene una mascota con ese nombre.');
                    }
                },
            ],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'type_id' => ['required', 'integer', 'exists:types,id'],
        ];
    }
}
