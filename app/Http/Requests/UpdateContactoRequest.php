<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación aplicadas al actualizar la información de contacto.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cc_telefono' => 'required|array|min:1',
            'cc_telefono.*' => 'required|string|max:50',
            'cc_dia_inicio' => 'required|string|max:50',
            'cc_dia_fin' => 'required|string|max:50',
            'cc_hora_apertura' => 'required|string',
            'cc_hora_cierre' => 'required|string',
            'cc_direccion' => 'nullable|string|max:500',
            'cc_email' => 'required|array|min:1',
            'cc_email.*' => 'required|email|max:100',
            'cc_contenido' => 'nullable',
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cc_telefono.required' => 'Debes registrar al menos un número telefónico de contacto.',
            'cc_telefono.min' => 'Debes registrar al menos un número telefónico.',
            'cc_telefono.*.required' => 'El número de teléfono no puede estar vacío.',
            'cc_dia_inicio.required' => 'El día inicial de atención es obligatorio.',
            'cc_dia_fin.required' => 'El día final de atención es obligatorio.',
            'cc_hora_apertura.required' => 'La hora de apertura es obligatoria.',
            'cc_hora_cierre.required' => 'La hora de cierre es obligatoria.',
            'cc_email.required' => 'Debes registrar al menos un correo electrónico de contacto.',
            'cc_email.min' => 'Debes registrar al menos un correo electrónico.',
            'cc_email.*.required' => 'El correo electrónico no puede estar vacío.',
            'cc_email.*.email' => 'Cada correo debe tener un formato de email válido.',
        ];
    }
}
