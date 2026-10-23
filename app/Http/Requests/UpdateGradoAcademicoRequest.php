<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradoAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ga_nombre' => 'required|string|max:255',
            'ga_abreviatura' => 'required|string|max:50',
            'ga_descripcion' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'ga_nombre.required' => 'El nombre del grado académico es obligatorio.',
            'ga_abreviatura.required' => 'La abreviatura es obligatoria.',
            'ga_abreviatura.max' => 'La abreviatura no debe superar los 50 caracteres.',
        ];
    }
}
