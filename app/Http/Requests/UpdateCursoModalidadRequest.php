<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCursoModalidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mc_nombre' => 'required|string|max:255',
            'mc_descripcion' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'mc_nombre.required' => 'El nombre de la modalidad es obligatorio.',
            'mc_nombre.max' => 'El nombre no debe superar los 255 caracteres.',
        ];
    }
}
