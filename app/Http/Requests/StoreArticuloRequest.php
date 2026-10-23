<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user && $user->hasRole('autor') && ! $user->hasRole('administrador')) {
            $this->merge([
                'art_fecha_recepcion' => now()->toDateString(),
                'art_fecha_aceptado' => null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'tipo_articulo_id' => 'nullable|integer|exists:tipo_articulos,id',
            'edicion_varianza_id' => 'nullable|integer|exists:ediciones_varianzas,id',
            'art_titulo' => 'required|string|max:255',
            'art_slug' => 'nullable|string|max:255|unique:articulos,art_slug',
            'art_resumen' => 'nullable|string',
            'art_abstracto' => 'nullable|string',
            'art_fecha_recepcion' => 'required|date',
            'art_fecha_aceptado' => 'nullable|date|after_or_equal:art_fecha_recepcion',
            'art_orcid' => 'nullable|string|max:255|unique:articulos,art_orcid',
            'art_doi' => 'nullable|string|max:255|unique:articulos,art_doi',
            'art_num_paginas' => 'nullable|integer|min:1',
            'art_pag_inicio' => 'nullable|integer|min:1',
            'art_pag_fin' => 'nullable|integer|min:1|gte:art_pag_inicio',
            'art_estado' => 'nullable|boolean',
            'art_referencias' => 'nullable|string',
            'art_vistas' => 'nullable|integer|min:0',
            'art_archivo' => 'nullable|file|mimes:pdf,doc,docx|max:15360',
            'etiquetas' => 'nullable|array',
            'etiquetas.*' => 'integer|exists:etiquetas_art,id',
            'autores' => 'nullable|array',
            'autores.*.autor_id' => 'required_with:autores|integer|exists:cms_autores,id',
            'autores.*.tipo_participaciones_id' => 'nullable|integer|exists:tipo_participaciones,id',
        ];
    }

    public function messages(): array
    {
        return [
            'art_titulo.required' => 'El título del artículo es obligatorio.',
            'art_slug.unique' => 'Este slug o identificador de URL ya está en uso.',
            'art_fecha_recepcion.required' => 'La fecha de recepción es obligatoria.',
            'art_fecha_aceptado.after_or_equal' => 'La fecha de aceptación debe ser posterior o igual a la de recepción.',
            'art_orcid.unique' => 'Este identificador ORCID ya se encuentra registrado.',
            'art_doi.unique' => 'Este identificador DOI ya se encuentra registrado.',
            'art_archivo.mimes' => 'El archivo adjunto debe estar en formato PDF o Word (DOC/DOCX).',
            'art_archivo.max' => 'El archivo adjunto no debe exceder los 15 MB.',
            'tipo_articulo_id.exists' => 'El tipo de artículo seleccionado no es válido.',
            'edicion_varianza_id.exists' => 'La edición de Revista Varianza seleccionada no es válida.',
            'art_pag_fin.gte' => 'La página final debe ser mayor o igual a la página inicial.',
            'etiquetas.*.exists' => 'Una o más de las etiquetas seleccionadas no son válidas.',
        ];
    }
}
