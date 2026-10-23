<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEtiquetaArtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $etiquetaParam = $this->route('etiquetas_art')
            ?? $this->route('etiqueta_art')
            ?? $this->route('etiqueta')
            ?? $this->route('etiquetasArt')
            ?? $this->route('id');
        $etiquetaId = is_object($etiquetaParam) ? ($etiquetaParam->id ?? null) : (int) $etiquetaParam;

        return [
            'ea_nombre' => 'required|string|max:150',
            'ea_slug' => [
                'nullable',
                'string',
                'max:150',
                Rule::unique('etiquetas_art', 'ea_slug')->ignore($etiquetaId),
            ],
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
