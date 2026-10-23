<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsComunicadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->com_titulo,
            'descripcion' => $this->com_descripcion,
            'enlace_url' => $this->com_enlace_url,
            'texto_boton' => $this->com_texto_boton,
            'tipo' => $this->com_tipo,
            'fecha_inicio' => $this->com_fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $this->com_fecha_fin?->format('Y-m-d'),
            'activo' => (bool) $this->com_activo,
            'vistas' => (int) $this->com_vistas,
            'flyer_url' => $this->flyer_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
