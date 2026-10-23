<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoArticuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoParam = $this->route('tipo_articulo')
            ?? $this->route('tipoArticulo')
            ?? $this->route('tipo_articulos')
            ?? $this->route('id');
        $tipoId = is_object($tipoParam) ? ($tipoParam->id ?? null) : (int) $tipoParam;

        return [
            'tar_nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tipo_articulos', 'tar_nombre')->ignore($tipoId),
            ],
            'tar_descripcion' => 'nullable|string',
            'tar_estado' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'tar_nombre.required' => 'El nombre del tipo de artículo es obligatorio.',
            'tar_nombre.unique' => 'Este tipo de artículo ya se encuentra registrado.',
        ];
    }
}
