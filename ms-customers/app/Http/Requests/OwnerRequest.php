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

    /** Mismos textos que el monolito (Bean Validation + messages.properties), para paridad en la UI. */
    public function messages(): array
    {
        return [
            '*.required' => 'must not be blank',
            'first_name.max' => 'size must be between 0 and 30',
            'last_name.max' => 'size must be between 0 and 30',
            'address.max' => 'size must be between 0 and 255',
            'city.max' => 'size must be between 0 and 80',
            'telephone.regex' => 'Telephone must be a 10-digit number',
        ];
    }
}
