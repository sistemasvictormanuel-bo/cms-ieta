<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoAutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ta_nombre' => 'required|string|max:255',
            'ta_descripcion' => 'nullable|string',
            'ta_estado' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'ta_nombre.required' => 'El tipo de autor es obligatorio.',
            'ta_nombre.max' => 'El tipo de autor no debe superar los 255 caracteres.',
        ];
    }
}
