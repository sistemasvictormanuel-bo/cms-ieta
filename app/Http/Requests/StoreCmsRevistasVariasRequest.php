<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCmsRevistasVariasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara los datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('rev_activo')) {
            $this->merge([
                'rev_activo' => filter_var($this->rev_activo, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            ]);
        }

        if ($this->has('rev_orden') && $this->rev_orden !== null) {
            $this->merge([
                'rev_orden' => (int) $this->rev_orden,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rev_titulo' => ['required', 'string', 'max:255'],
            'rev_descripcion' => ['nullable', 'string'],
            'rev_url_enlace' => ['required', 'url', 'max:500'],
            'rev_entidad' => ['nullable', 'string', 'max:255'],
            'rev_issn' => ['nullable', 'string', 'max:100'],
            'rev_activo' => ['nullable', 'boolean'],
            'rev_orden' => ['nullable', 'integer', 'min:0'],
            'portada' => ['nullable', 'image', 'mimes:jpeg,png,webp,avif,jpg', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rev_titulo.required' => 'El título de la revista es obligatorio.',
            'rev_url_enlace.required' => 'La dirección URL o enlace web de la revista es obligatorio.',
            'rev_url_enlace.url' => 'El enlace debe ser una URL válida (ej. https://ejemplo.com).',
            'portada.image' => 'El archivo de portada debe ser una imagen válida.',
            'portada.max' => 'La imagen no puede pesar más de 10 MB.',
        ];
    }
}
