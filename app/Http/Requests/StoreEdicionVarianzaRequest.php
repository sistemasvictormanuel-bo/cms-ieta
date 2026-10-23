<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEdicionVarianzaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ev_titulo' => 'required|string|max:255',
            'ev_numero' => 'required|string|max:50',
            'ev_volumen' => 'nullable|string|max:50',
            'ev_anio' => 'required|integer|min:1950|max:2100',
            'ev_mes' => 'nullable|string|max:100',
            'ev_slug' => 'nullable|string|max:255|unique:ediciones_varianzas,ev_slug',
            'ev_issn_ver_impresa' => 'nullable|string|max:50',
            'ev_issn_ver_electronica' => 'nullable|string|max:50',
            'ev_num_art_originales' => 'nullable|integer|min:0',
            'ev_notas_cientificas' => 'nullable|integer|min:0',
            'ev_num_paginas' => 'nullable|integer|min:1',
            'ev_doi' => 'nullable|string|max:255',
            'ev_presentacion' => 'nullable',
            'ev_editorial' => 'nullable|string',
            'ev_fecha_publicacion' => 'nullable|date',
            'ev_estado' => 'nullable|boolean',
            'ev_portada' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:5120',
            'ev_archivo' => 'nullable|file|mimes:pdf|max:30720',
        ];
    }

    public function messages(): array
    {
        return [
            'ev_titulo.required' => 'El título de la edición es obligatorio.',
            'ev_numero.required' => 'El número de la edición es obligatorio.',
            'ev_anio.required' => 'El año de publicación es obligatorio.',
            'ev_slug.unique' => 'Este slug ya se encuentra registrado por otra edición.',
            'ev_portada.image' => 'La portada debe ser una imagen válida (JPEG, PNG o WEBP).',
            'ev_portada.max' => 'La portada no debe superar los 5 MB.',
            'ev_archivo.mimes' => 'El archivo completo de la edición debe estar en formato PDF.',
            'ev_archivo.max' => 'El archivo PDF no debe exceder los 30 MB.',
        ];
    }
}
