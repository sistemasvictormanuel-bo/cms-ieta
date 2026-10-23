<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemoriasInvestigacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'autor_id' => 'required|integer|exists:cms_autores,id',
            'mdi_titulo' => 'required|string|max:255',
            'mdi_descripcion' => 'nullable|string',
            'mdi_etiqueta' => 'required|string|max:100',
            'mdi_fecha' => 'required|date',
            'mdi_enlace_url' => 'nullable|string|max:2048',
            'mdi_archivo' => 'nullable|file|max:51200',
        ];
    }

    public function messages(): array
    {
        return [
            'autor_id.required' => 'Debes seleccionar un autor o investigador.',
            'autor_id.exists' => 'El autor seleccionado no existe.',
            'mdi_titulo.required' => 'El título de la memoria o investigación es obligatorio.',
            'mdi_etiqueta.required' => 'La etiqueta o categoría es obligatoria.',
            'mdi_fecha.required' => 'La fecha de la memoria es obligatoria.',
            'mdi_archivo.max' => 'El archivo adjunto no debe superar los 50 MB.',
        ];
    }
}
