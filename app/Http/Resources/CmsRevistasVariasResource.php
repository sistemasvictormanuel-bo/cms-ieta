<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsRevistasVariasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->rev_titulo,
            'descripcion' => $this->rev_descripcion,
            'url_enlace' => $this->rev_url_enlace,
            'entidad' => $this->rev_entidad,
            'issn' => $this->rev_issn,
            'activo' => (bool) $this->rev_activo,
            'orden' => (int) $this->rev_orden,
            'portada_url' => $this->portada_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
