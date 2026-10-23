<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('crear_usuario')) {
            $this->merge([
                'crear_usuario' => filter_var($this->crear_usuario, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('user_is_active')) {
            $this->merge([
                'user_is_active' => filter_var($this->user_is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        $crearUsuario = $this->has('crear_usuario')
            ? filter_var($this->input('crear_usuario'), FILTER_VALIDATE_BOOLEAN)
            : false;

        $rules = [
            // Datos de Persona
            'p_nombre' => 'required|string|max:150',
            'p_apellido_paterno' => 'required|string|max:150',
            'p_apellido_materno' => 'nullable|string|max:150',
            'p_celular' => 'nullable|string|max:50',
            'p_sitio_web' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

            // Datos de Usuario opcional
            'crear_usuario' => 'nullable|boolean',
            'user_is_active' => 'nullable|boolean',

            // Datos de Personal
            'cargo_id' => 'required|integer|exists:cargo_institucionales,id',
            'pers_descripcion' => 'nullable|string',
            'pers_fecha_ingreso' => 'nullable|date',
            'pers_fecha_retiro' => 'nullable|date|after_or_equal:pers_fecha_ingreso',
            'pers_estado' => 'nullable|boolean',
        ];

        if ($crearUsuario) {
            $rules['p_correo'] = 'required|email|max:255|unique:personas,p_correo|unique:users,email';
            $rules['password'] = 'required|string|min:8';
            $rules['rol'] = 'nullable|string|exists:roles,name';
        } else {
            $rules['p_correo'] = 'required|email|max:255|unique:personas,p_correo';
            $rules['password'] = 'nullable|string';
            $rules['rol'] = 'nullable|string';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'p_nombre.required' => 'El nombre de la persona es obligatorio.',
            'p_apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'p_correo.required' => 'El correo electrónico es obligatorio.',
            'p_correo.email' => 'Debe ingresar un correo electrónico válido.',
            'p_correo.unique' => 'Este correo electrónico ya está registrado en el sistema.',
            'password.required' => 'La contraseña es obligatoria al dar de alta una cuenta.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'rol.exists' => 'El rol seleccionado no es válido.',
            'cargo_id.required' => 'Debes seleccionar un cargo institucional.',
            'cargo_id.exists' => 'El cargo seleccionado no es válido.',
            'pers_fecha_retiro.after_or_equal' => 'La fecha de retiro debe ser posterior o igual a la fecha de ingreso.',
        ];
    }
}
