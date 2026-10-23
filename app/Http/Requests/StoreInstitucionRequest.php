<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inst_nombre' => 'required|string|max:255',
            'inst_pais' => 'required|string|max:100',
            'inst_ciudad' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'inst_nombre.required' => 'El nombre de la institución es obligatorio.',
            'inst_pais.required' => 'El país de la institución es obligatorio.',
            'inst_ciudad.required' => 'La ciudad de la institución es obligatoria.',
        ];
    }
}
