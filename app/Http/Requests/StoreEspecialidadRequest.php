<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ep_nombre' => 'required|string|max:255',
            'ep_descripcion' => 'nullable|string',
            'ep_estado' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'ep_nombre.required' => 'El nombre de la especialidad es obligatorio.',
            'ep_nombre.max' => 'El nombre no debe superar los 255 caracteres.',
        ];
    }
}
