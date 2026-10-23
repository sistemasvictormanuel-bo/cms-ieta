<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsReglamentoInternoResource extends JsonResource
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
            'titulo' => $this->reg_titulo,
            'descripcion' => $this->reg_descripcion,
            'contenido' => $this->reg_contenido,
            'archivo_url' => $this->archivo_url,
            'archivo_nombre' => $this->archivo_nombre,
            'archivo_size' => $this->archivo_size,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
