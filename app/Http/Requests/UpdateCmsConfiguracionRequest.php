<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsConfiguracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cfg_nombre_institucion' => 'required|string|max:255',
            'cfg_siglas' => 'required|string|max:50',
            'cfg_slogan' => 'nullable|string|max:255',
            'cfg_descripcion_corta' => 'nullable|string|max:1000',
            'cfg_email_principal' => 'nullable|email|max:255',
            'cfg_telefono_principal' => 'nullable|string|max:100',
            'cfg_direccion_principal' => 'nullable|string|max:255',
            'cfg_redes_sociales' => 'nullable|array',
            'cfg_copyright' => 'nullable|string|max:255',
            'cfg_logo_principal' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'cfg_logo_blanco' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'cfg_favicon' => 'nullable|file|mimes:ico,png,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'cfg_nombre_institucion.required' => 'El nombre de la institución es obligatorio.',
            'cfg_siglas.required' => 'Las siglas institucionales son obligatorias.',
            'cfg_email_principal.email' => 'Debe ingresar un correo electrónico válido.',
            'cfg_logo_principal.max' => 'El logo principal no debe superar los 5 MB.',
            'cfg_logo_blanco.max' => 'El logo blanco no debe superar los 5 MB.',
            'cfg_favicon.max' => 'El favicon no debe superar los 2 MB.',
        ];
    }
}
