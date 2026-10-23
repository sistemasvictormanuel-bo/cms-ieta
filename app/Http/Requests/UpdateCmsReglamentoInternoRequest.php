<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsReglamentoInternoRequest extends FormRequest
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
        if ($this->has('reg_contenido') && is_string($this->input('reg_contenido'))) {
            $decoded = json_decode($this->input('reg_contenido'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['reg_contenido' => $decoded]);
            }
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
            'reg_titulo' => ['required', 'string', 'max:255'],
            'reg_descripcion' => ['nullable', 'string'],
            'reg_contenido' => ['nullable', 'array'],
            'archivo_reglamento' => ['nullable', 'file', 'mimes:pdf', 'max:25600'], // hasta 25MB
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reg_titulo.required' => 'El título del reglamento institucional es obligatorio.',
            'archivo_reglamento.mimes' => 'El documento de reglamento debe ser un archivo en formato PDF.',
            'archivo_reglamento.max' => 'El documento PDF no puede superar los 25 MB.',
        ];
    }
}
