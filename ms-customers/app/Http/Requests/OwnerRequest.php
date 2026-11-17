<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Reglas de Owner.java (docs/fase1/er-diagram.md). Se usa en alta y edición. */
class OwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:30'],
            'last_name' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:80'],
            'telephone' => ['required', 'string', 'regex:/^\d{10}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'telephone.regex' => 'El teléfono debe tener exactamente 10 dígitos.',
        ];
    }
}
