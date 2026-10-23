<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLineaInvestigacion extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'li_titulo' => 'sometimes|required|string|max:255',
            'li_descripcion' => 'sometimes|required|string',
            'li_contenido' => 'sometimes|array',
            'li_imagen' => 'nullable|sometimes|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'li_titulo.required' => 'El título de la línea es obligatorio.',
            'li_descripcion.required' => 'La descripción es obligatoria.',
            'li_contenido.required' => 'El contenido de las líneas de investigación es obligatorio.',
            'li_imagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'li_imagen.mimes' => 'La imagen debe estar en formato JPEG, PNG, JPG, WEBP o SVG.',
            'li_imagen.max' => 'La imagen no debe superar los 2 MB.',
        ];
    }
}
