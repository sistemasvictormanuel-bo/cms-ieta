<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EdicionVarianzaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->ev_titulo,
            'numero' => $this->ev_numero,
            'volumen' => $this->ev_volumen,
            'anio' => $this->ev_anio,
            'mes' => $this->ev_mes,
            'slug' => $this->ev_slug,
            'issn_impresa' => $this->ev_issn_ver_impresa,
            'issn_electronica' => $this->ev_issn_ver_electronica,
            'num_art_originales_declarado' => $this->ev_num_art_originales,
            'notas_cientificas_declarado' => $this->ev_notas_cientificas,
            'num_paginas' => $this->ev_num_paginas,
            'doi' => $this->ev_doi,
            'presentacion' => $this->ev_presentacion,
            'editorial' => $this->ev_editorial,
            'fecha_publicacion' => $this->ev_fecha_publicacion?->format('Y-m-d'),
            'estado' => (bool) $this->ev_estado,
            'portada_url' => $this->portada_url,
            'archivo_url' => $this->archivo_url,
            'articulos_count' => $this->articulos_count ?? $this->whenLoaded('articulos', fn () => $this->articulos->count()),
            'articulos_originales_count' => $this->articulos_originales_count ?? 0,
            'notas_cientificas_count' => $this->notas_cientificas_count ?? 0,
            'articulos' => ArticuloResource::collection($this->whenLoaded('articulos')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
