<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Lectura en lote: ?pet_ids=1,2,3 (obligatorio, máx. 50). docs/fase2 §4. */
class IndexVisitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->query('pet_ids');

        if (is_string($raw)) {
            $this->merge([
                'pet_ids' => $raw === '' ? [] : array_map('trim', explode(',', $raw)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'pet_ids' => ['required', 'array', 'max:50'],
            'pet_ids.*' => ['integer', 'min:1'],
        ];
    }

    /** @return list<int> */
    public function petIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->validated('pet_ids'))));
    }
}
