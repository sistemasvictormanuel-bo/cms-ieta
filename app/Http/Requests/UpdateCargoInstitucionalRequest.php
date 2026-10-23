<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCargoInstitucionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ci_nombre' => 'required|string|max:255',
            'ci_descripcion' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'ci_nombre.required' => 'El nombre del cargo institucional es obligatorio.',
        ];
    }
}
