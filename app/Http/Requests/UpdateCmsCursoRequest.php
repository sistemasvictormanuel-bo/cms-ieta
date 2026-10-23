<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCmsCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cursoParam = $this->route('curso')
            ?? $this->route('cursos')
            ?? $this->route('cms_curso')
            ?? $this->route('cms_cursos')
            ?? $this->route('id');
        $cursoId = is_object($cursoParam) ? ($cursoParam->id ?? null) : (int) $cursoParam;

        return [
            'ct_titulo' => 'required|string|max:255',
            'tipo_curso_id' => 'required|integer|exists:tipo_cursos,id',
            'modalidad_curso_id' => 'required|integer|exists:curso_modalidades,id',
            'docente_id' => 'nullable|integer|exists:personas,id',
            'ct_fecha_inicio' => 'required|date',
            'ct_fecha_fin' => 'required|date|after_or_equal:ct_fecha_inicio',
            'ct_hora_inicio' => 'nullable|string|max:20',
            'ct_hora_fin' => 'nullable|string|max:20',
            'ct_duracion' => 'nullable|string|max:100',
            'ct_descripcion' => 'nullable|string',
            'ct_contenido' => 'required|array',
            'ct_email_info' => 'nullable|email|max:255',
            'ct_telefono_ref' => 'nullable|string|max:30',
            'ct_slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('cms_cursos', 'ct_slug')->ignore($cursoId),
            ],
            'ct_activo' => 'nullable|boolean',
            'ct_costo' => 'nullable|numeric|min:0',
            'portada_curso' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'ct_titulo.required' => 'El título del curso es obligatorio.',
            'ct_titulo.max' => 'El título no debe superar los 255 caracteres.',
            'tipo_curso_id.required' => 'Debes seleccionar un tipo de curso.',
            'tipo_curso_id.exists' => 'El tipo de curso seleccionado no es válido.',
            'modalidad_curso_id.required' => 'Debes seleccionar una modalidad.',
            'modalidad_curso_id.exists' => 'La modalidad seleccionada no es válida.',
            'docente_id.exists' => 'El docente seleccionado no es válido.',
            'ct_fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'ct_fecha_fin.required' => 'La fecha de finalización es obligatoria.',
            'ct_fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'ct_contenido.required' => 'El contenido detallado del curso es obligatorio.',
            'ct_contenido.array' => 'El contenido debe tener una estructura de bloques válida.',
            'ct_email_info.email' => 'El correo electrónico de contacto debe tener un formato válido.',
            'ct_slug.unique' => 'Este slug o identificador ya está en uso.',
            'portada_curso.image' => 'El archivo de portada debe ser una imagen válida.',
            'portada_curso.mimes' => 'La portada debe tener formato JPEG, PNG, JPG o WEBP.',
            'portada_curso.max' => 'La imagen de portada no debe superar los 2 MB.',
        ];
    }
}
