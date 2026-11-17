<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Reglas de Visit.java + VisitController (docs/fase1/er-diagram.md). */
class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['required', 'integer', 'min:1'],
            'pet_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date_format:Y-m-d', 'after:today'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.after' => 'La fecha de la visita debe ser posterior a hoy.',
        ];
    }
}
