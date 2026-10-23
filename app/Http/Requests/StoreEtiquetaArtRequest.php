<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEtiquetaArtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ea_nombre' => 'required|string|max:150',
            'ea_slug' => 'nullable|string|max:150|unique:etiquetas_art,ea_slug',
        ];
    }

    public function messages(): array
    {
        return [
            'ea_nombre.required' => 'El nombre de la etiqueta es obligatorio.',
            'ea_nombre.max' => 'El nombre no debe superar los 150 caracteres.',
            'ea_slug.unique' => 'Este slug ya está en uso por otra etiqueta.',
            'ea_slug.max' => 'El slug no debe superar los 150 caracteres.',
        ];
    }
}
