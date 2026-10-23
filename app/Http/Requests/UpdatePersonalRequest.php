<?php

namespace App\Http\Requests;

use App\Models\Personal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonalRequest extends FormRequest
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
        $personalParam = $this->route('personal') ?? $this->route('id');
        $personalId = is_object($personalParam) ? ($personalParam->id ?? null) : (int) $personalParam;

        $personal = $personalParam instanceof Personal ? $personalParam : Personal::with('persona.user')->find($personalId);
        $personaId = $personal?->persona_id;
        $userId = $personal?->persona?->user_id;

        $crearUsuario = filter_var($this->input('crear_usuario', false), FILTER_VALIDATE_BOOLEAN);

        $rules = [
            // Datos de Persona
            'p_nombre' => 'required|string|max:150',
            'p_apellido_paterno' => 'required|string|max:150',
            'p_apellido_materno' => 'nullable|string|max:150',
            'p_celular' => 'nullable|string|max:50',
            'p_sitio_web' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

            // Datos de Usuario
            'crear_usuario' => 'nullable|boolean',
            'user_is_active' => 'nullable|boolean',
            'rol' => 'nullable|string|exists:roles,name',

            // Datos de Personal
            'cargo_id' => 'required|integer|exists:cargo_institucionales,id',
            'pers_descripcion' => 'nullable|string',
            'pers_fecha_ingreso' => 'nullable|date',
            'pers_fecha_retiro' => 'nullable|date|after_or_equal:pers_fecha_ingreso',
            'pers_estado' => 'nullable|boolean',
        ];

        if ($userId) {
            $rules['p_correo'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('personas', 'p_correo')->ignore($personaId),
                Rule::unique('users', 'email')->ignore($userId),
            ];
            $rules['password'] = 'nullable|string|min:8';
        } elseif ($crearUsuario) {
            $rules['p_correo'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('personas', 'p_correo')->ignore($personaId),
                Rule::unique('users', 'email'),
            ];
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['p_correo'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('personas', 'p_correo')->ignore($personaId),
            ];
            $rules['password'] = 'nullable|string';
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
            'cargo_id.required' => 'Debes seleccionar un cargo institucional.',
            'cargo_id.exists' => 'El cargo seleccionado no es válido.',
            'pers_fecha_retiro.after_or_equal' => 'La fecha de retiro debe ser posterior o igual a la fecha de ingreso.',
        ];
    }
}
