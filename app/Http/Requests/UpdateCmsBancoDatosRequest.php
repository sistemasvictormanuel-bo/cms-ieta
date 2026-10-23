<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCmsBancoDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bancoParam = $this->route('banco_dato')
            ?? $this->route('cms_banco_dato')
            ?? $this->route('banco-dato')
            ?? $this->route('id');

        $id = is_object($bancoParam) ? ($bancoParam->id ?? null) : (int) $bancoParam;

        return [
            'bdd_tipo_dato' => 'required|in:Censos,Encuestas',
            'bdd_anio' => 'required|integer|min:1900|max:2100',
            'bdd_codigo' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cms_banco_datos', 'bdd_codigo')->ignore($id),
            ],
            'bdd_titulo' => 'required|string|max:255',
            'bdd_descripcion' => 'nullable|string',
            'bdd_locacion' => 'nullable|string|max:255',
            'bdd_url' => 'nullable|string|max:2048',
            'bdd_archivo' => 'nullable|file|max:51200',
            'bdd_portada' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'areas' => 'nullable|array',
            'areas.*' => 'integer|exists:area_tematicas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'bdd_tipo_dato.required' => 'Debes seleccionar el tipo de dato (Censos o Encuestas).',
            'bdd_anio.required' => 'El año es obligatorio.',
            'bdd_codigo.required' => 'El código de la base de datos es obligatorio.',
            'bdd_codigo.unique' => 'Este código ya está registrado en el banco de datos.',
            'bdd_titulo.required' => 'El título de la base de datos es obligatorio.',
            'bdd_archivo.max' => 'El archivo adjunto no debe superar los 50 MB.',
            'bdd_portada.max' => 'La imagen de portada no debe superar los 5 MB.',
        ];
    }
}
