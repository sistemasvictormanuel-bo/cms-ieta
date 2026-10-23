<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsGaleriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('gal_destacado')) {
            $this->merge([
                'gal_destacado' => filter_var($this->gal_destacado, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('gal_activo')) {
            $this->merge([
                'gal_activo' => filter_var($this->gal_activo, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('gal_fecha') && trim((string) $this->gal_fecha) === '') {
            $this->merge(['gal_fecha' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'gal_titulo' => ['required', 'string', 'max:255'],
            'gal_descripcion' => ['nullable', 'string', 'max:2000'],
            'gal_categoria' => ['required', 'string', 'max:100'],
            'gal_fecha' => ['nullable', 'date'],
            'gal_lugar' => ['nullable', 'string', 'max:255'],
            'gal_destacado' => ['nullable', 'boolean'],
            'gal_activo' => ['nullable', 'boolean'],
            'gal_orden' => ['nullable', 'integer', 'min:0'],
            'gal_imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,avif', 'max:15360'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,avif', 'max:15360'],
        ];
    }

    public function messages(): array
    {
        return [
            'gal_titulo.required' => 'El título de la imagen es obligatorio.',
            'gal_titulo.max' => 'El título no debe superar los 255 caracteres.',
            'gal_categoria.required' => 'Debes seleccionar o especificar una categoría.',
            'gal_fecha.date' => 'La fecha debe tener un formato válido.',
            'imagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.mimes' => 'La imagen debe estar en formato JPEG, PNG, JPG, WEBP o AVIF.',
            'imagen.max' => 'La imagen no debe superar los 15 MB de tamaño.',
            'gal_imagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'gal_imagen.mimes' => 'La imagen debe estar en formato JPEG, PNG, JPG, WEBP o AVIF.',
            'gal_imagen.max' => 'La imagen no debe superar los 15 MB de tamaño.',
        ];
    }
}
