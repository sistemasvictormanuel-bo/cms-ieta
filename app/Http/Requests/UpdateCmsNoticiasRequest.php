<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsNoticiasRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara los datos antes de la validación (normaliza JSON y booleanos de FormData).
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->not_contenido)) {
            $decoded = json_decode($this->not_contenido, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['not_contenido' => $decoded]);
            }
        }

        if ($this->has('not_destacado')) {
            $this->merge([
                'not_destacado' => filter_var($this->not_destacado, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('not_publicado')) {
            $this->merge([
                'not_publicado' => filter_var($this->not_publicado, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('persona_id') && ($this->persona_id === '' || $this->persona_id === 'null')) {
            $this->merge(['persona_id' => null]);
        }

        if ($this->has('not_fecha') && trim((string) $this->not_fecha) === '') {
            $this->merge(['not_fecha' => null]);
        }
    }

    /**
     * Reglas de validación aplicadas al actualizar una noticia.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'persona_id' => ['nullable', 'integer', 'exists:personas,id'],
            'not_titulo' => ['required', 'string', 'max:255'],
            'not_descripcion' => ['nullable', 'string', 'max:1000'],
            'not_fecha' => ['nullable', 'date'],
            'not_destacado' => ['nullable', 'boolean'],
            'not_publicado' => ['nullable', 'boolean'],
            'not_contenido' => ['nullable', 'array'],
            'not_url' => ['nullable', 'url', 'max:2048'],
            'not_portada' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,gif', 'max:10240'],
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
            'persona_id.exists' => 'La persona o autor seleccionado no es válido.',
            'not_titulo.required' => 'El título de la noticia es obligatorio.',
            'not_titulo.max' => 'El título no debe superar los 255 caracteres.',
            'not_descripcion.max' => 'La descripción breve no debe superar los 1000 caracteres.',
            'not_fecha.date' => 'La fecha debe ser un formato de fecha válido.',
            'not_contenido.array' => 'El contenido debe tener una estructura de bloques válida.',
            'not_url.url' => 'El enlace externo debe ser una URL válida (ej. https://...).',
            'not_portada.image' => 'El archivo seleccionado para portada debe ser una imagen.',
            'not_portada.mimes' => 'La portada debe tener formato JPEG, PNG, JPG, WEBP, SVG o GIF.',
            'not_portada.max' => 'La imagen de portada no debe superar los 10 MB de tamaño.',
        ];
    }
}
