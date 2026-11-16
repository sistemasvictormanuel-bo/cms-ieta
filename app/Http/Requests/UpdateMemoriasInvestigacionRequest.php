<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemoriasInvestigacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mdi_fecha') && ! $this->filled('mdi_anio')) {
            $fecha = (string) $this->input('mdi_fecha');
            $this->merge(['mdi_anio' => (int) substr($fecha, 0, 4)]);
        }
    }

    public function rules(): array
    {
        return [
            'autor_id' => 'required|integer|exists:cms_autores,id',
            'mdi_titulo' => 'required|string|max:2000',
            'mdi_descripcion' => 'nullable|string',
            'mdi_etiqueta' => 'required|string|max:100',
            'mdi_anio' => 'required|integer|min:1950|max:'.(date('Y') + 5),
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
            'mdi_anio.required' => 'El año o gestión de la memoria es obligatorio.',
            'mdi_anio.integer' => 'El año o gestión debe ser un número entero válido.',
            'mdi_anio.min' => 'El año o gestión no es válido.',
            'mdi_anio.max' => 'El año o gestión no puede ser superior a :max.',
            'mdi_archivo.max' => 'El archivo adjunto no debe superar los 50 MB.',
        ];
    }
}
