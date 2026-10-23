<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tc_nombre' => 'required|string|max:255',
            'tc_descripcion' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tc_nombre.required' => 'El nombre del tipo de curso es obligatorio.',
            'tc_nombre.max' => 'El nombre no debe superar los 255 caracteres.',
        ];
    }
}
