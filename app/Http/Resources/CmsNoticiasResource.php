<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsNoticiasResource extends JsonResource
{
    /**
     * Transforma el recurso en un array serializable para la API.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'persona_id' => $this->persona_id,
            'titulo' => $this->not_titulo,
            'not_titulo' => $this->not_titulo,
            'descripcion' => $this->not_descripcion,
            'not_descripcion' => $this->not_descripcion,
            'fecha' => $this->not_fecha?->format('Y-m-d'),
            'not_fecha' => $this->not_fecha?->format('Y-m-d'),
            'destacado' => (bool) $this->not_destacado,
            'not_destacado' => (bool) $this->not_destacado,
            'publicado' => (bool) $this->not_publicado,
            'not_publicado' => (bool) $this->not_publicado,
            'contenido' => $this->not_contenido,
            'not_contenido' => $this->not_contenido,
            'url' => $this->not_url,
            'not_url' => $this->not_url,
            'portada_url' => $this->portada_url,
            'persona' => $this->whenLoaded('persona'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
