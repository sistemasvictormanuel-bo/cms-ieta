<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstruccionAutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ia_titulo_seccion' => ['required', 'string', 'max:150'],
            'ia_contenido' => ['nullable'],
            'ia_orden' => ['nullable', 'integer', 'min:0'],
            'ia_activo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'ia_titulo_seccion.required' => 'El título de la sección es obligatorio.',
            'ia_titulo_seccion.max' => 'El título no puede superar los 150 caracteres.',
            'ia_orden.integer' => 'El orden numérico debe ser un número entero.',
            'ia_orden.min' => 'El orden no puede ser menor a 0.',
        ];
    }
}
