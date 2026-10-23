<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsRevistaVarianzaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rv_titulo' => 'required|string|max:255',
            'rv_subtitulo' => 'nullable|string|max:255',
            'rv_mision' => 'nullable|string',
            'rv_vision' => 'nullable|string',
            'rv_contenido' => 'nullable',
            'rv_alcance' => 'nullable',
            'rv_politicas' => 'nullable',
            'rv_issn_impreso' => 'nullable|string|max:50',
            'rv_issn_electronico' => 'nullable|string|max:50',
            'rv_periodicidad' => 'nullable|string|max:100',
            'rv_correo_contacto' => 'nullable|email|max:150',
            'rv_portada' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'rv_titulo.required' => 'El título de la revista es obligatorio.',
            'rv_correo_contacto.email' => 'El correo electrónico de contacto debe ser una dirección válida.',
            'rv_portada.image' => 'El archivo de portada debe ser una imagen válida (JPEG, PNG o WEBP).',
            'rv_portada.max' => 'La imagen de portada no debe exceder los 5 MB.',
        ];
    }
}
