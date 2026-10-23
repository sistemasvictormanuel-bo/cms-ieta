<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsGaleriaResource extends JsonResource
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
            'titulo' => $this->gal_titulo,
            'descripcion' => $this->gal_descripcion,
            'categoria' => $this->gal_categoria,
            'fecha' => $this->gal_fecha ? $this->gal_fecha->format('Y-m-d') : null,
            'fecha_formateada' => $this->gal_fecha ? $this->gal_fecha->translatedFormat('d M, Y') : null,
            'lugar' => $this->gal_lugar,
            'destacado' => (bool) $this->gal_destacado,
            'activo' => (bool) $this->gal_activo,
            'orden' => $this->gal_orden,
            'imagen_url' => $this->imagen_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
