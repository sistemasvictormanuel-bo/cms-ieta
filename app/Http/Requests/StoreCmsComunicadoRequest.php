<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCmsComunicadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'com_titulo' => 'required|string|max:255',
            'com_descripcion' => 'nullable|string',
            'com_enlace_url' => 'nullable|string|max:500',
            'com_texto_boton' => 'nullable|string|max:100',
            'com_tipo' => 'required|string|in:general,curso,articulo,convocatoria,noticia',
            'com_fecha_inicio' => 'required|date',
            'com_fecha_fin' => 'nullable|date|after_or_equal:com_fecha_inicio',
            'com_activo' => 'nullable|boolean',
            'com_flyer' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'com_titulo.required' => 'El título del comunicado es obligatorio.',
            'com_tipo.required' => 'El tipo de novedad es obligatorio.',
            'com_fecha_inicio.required' => 'La fecha de inicio de vigencia es obligatoria.',
            'com_fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
            'com_flyer.image' => 'El afiche debe ser una imagen válida.',
            'com_flyer.max' => 'El afiche no debe superar los 10 MB.',
        ];
    }
}
