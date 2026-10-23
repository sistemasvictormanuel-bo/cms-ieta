<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoParticipacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tp_nombre' => 'required|string|max:255',
            'tp_descripcion' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tp_nombre.required' => 'El nombre del tipo de participación es obligatorio.',
            'tp_nombre.max' => 'El nombre no debe superar los 255 caracteres.',
        ];
    }
}
