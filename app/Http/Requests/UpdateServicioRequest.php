<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación aplicadas al actualizar un servicio existente.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sv_titulo' => 'sometimes|required|string|max:255',
            'sv_descripcion' => 'nullable|string',
            'sv_contenido' => 'sometimes|required|array',
            'sv_imagen' => 'nullable|sometimes|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
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
            'sv_titulo.required' => 'El título del servicio es obligatorio.',
            'sv_titulo.max' => 'El título no debe exceder los 255 caracteres.',
            'sv_contenido.required' => 'El contenido detallado del servicio es obligatorio.',
            'sv_contenido.array' => 'El contenido debe tener una estructura de bloques válida.',
            'sv_imagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'sv_imagen.mimes' => 'La imagen debe tener formato JPEG, PNG, JPG, WEBP o SVG.',
            'sv_imagen.max' => 'La imagen no debe superar los 2 MB de tamaño.',
        ];
    }
}
