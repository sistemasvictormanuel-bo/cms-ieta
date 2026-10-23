<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaTematicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'at_nombre' => 'required|string|max:255',
            'at_descripcion' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'at_nombre.required' => 'El nombre del área temática es obligatorio.',
        ];
    }
}
