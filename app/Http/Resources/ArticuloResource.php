<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticuloResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_articulo_id' => $this->tipo_articulo_id,
            'tipo_articulo' => new TipoArticuloResource($this->whenLoaded('tipoArticulo')),
            'edicion_varianza_id' => $this->edicion_varianza_id,
            'edicion' => $this->whenLoaded('edicion', function () {
                return [
                    'id' => $this->edicion->id,
                    'titulo' => $this->edicion->ev_titulo,
                    'numero' => $this->edicion->ev_numero,
                    'volumen' => $this->edicion->ev_volumen,
                    'anio' => $this->edicion->ev_anio,
                    'slug' => $this->edicion->ev_slug,
                    'portada_url' => $this->edicion->portada_url,
                ];
            }),
            'titulo' => $this->art_titulo,
            'slug' => $this->art_slug,
            'resumen' => $this->art_resumen,
            'abstracto' => $this->art_abstracto,
            'fecha_recepcion' => $this->art_fecha_recepcion?->format('Y-m-d'),
            'fecha_aceptado' => $this->art_fecha_aceptado?->format('Y-m-d'),
            'orcid' => $this->art_orcid,
            'doi' => $this->art_doi,
            'num_paginas' => $this->art_num_paginas,
            'pag_inicio' => $this->art_pag_inicio,
            'pag_fin' => $this->art_pag_fin,
            'rango_paginas' => ($this->art_pag_inicio && $this->art_pag_fin) ? "pp. {$this->art_pag_inicio}-{$this->art_pag_fin}" : null,
            'estado' => (bool) $this->art_estado,
            'referencias' => $this->art_referencias,
            'etiquetas' => EtiquetaArtResource::collection($this->whenLoaded('etiquetas')),
            'etiquetas_array' => $this->etiquetas_list,
            'vistas' => $this->art_vistas ?? 0,
            'archivo_url' => $this->archivo_url,
            'autores' => AutorResource::collection($this->whenLoaded('autores')),
            'articulos_similares' => ArticuloResource::collection($this->whenLoaded('articulosSimilares')),
            'mas_leidos_autor' => ArticuloResource::collection($this->whenLoaded('masLeidosAutor')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
