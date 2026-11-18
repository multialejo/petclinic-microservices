<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Reglas de Visit.java + VisitController del monolito original. */
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
        // Mismos textos que el monolito (Visit.java y messages.properties).
        return [
            'description.required' => 'must not be blank',
            'description.max' => 'size must be between 0 and 255',
            'date.required' => 'is required',
            'date.date_format' => 'invalid date',
            'date.after' => 'Visit date must be in the future',
        ];
    }
}
