<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-\.]+$/',
                'unique:permissions,name',
            ],
            'modulo' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El identificador técnico del permiso es obligatorio.',
            'name.unique' => 'Ya existe un permiso con este identificador.',
            'name.regex' => 'El identificador técnico solo puede contener letras, números, puntos, guiones y guiones bajos (ej: cursos.crear).',
            'modulo.required' => 'El módulo o grupo del permiso es obligatorio.',
            'modulo.max' => 'El nombre del módulo no debe exceder los 100 caracteres.',
            'description.max' => 'La descripción no debe exceder los 255 caracteres.',
        ];
    }
}
