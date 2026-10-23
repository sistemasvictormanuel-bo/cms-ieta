<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoArticuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tar_nombre' => 'required|string|max:100|unique:tipo_articulos,tar_nombre',
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
