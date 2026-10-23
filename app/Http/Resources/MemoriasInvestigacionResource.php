<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemoriasInvestigacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'autor_id' => $this->autor_id,
            'titulo' => $this->mdi_titulo,
            'mdi_titulo' => $this->mdi_titulo,
            'descripcion' => $this->mdi_descripcion,
            'mdi_descripcion' => $this->mdi_descripcion,
            'etiqueta' => $this->mdi_etiqueta,
            'mdi_etiqueta' => $this->mdi_etiqueta,
            'fecha' => $this->mdi_fecha?->format('Y-m-d'),
            'mdi_fecha' => $this->mdi_fecha?->format('Y-m-d'),
            'enlace_url' => $this->mdi_enlace_url,
            'mdi_enlace_url' => $this->mdi_enlace_url,
            'archivo_url' => $this->archivo_url,
            'autor' => new AutorResource($this->whenLoaded('autor')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
