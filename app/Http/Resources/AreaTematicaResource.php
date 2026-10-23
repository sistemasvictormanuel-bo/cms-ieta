<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaTematicaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->at_nombre,
            'descripcion' => $this->at_descripcion,
            'banco_datos_count' => $this->whenCounted('bancoDatos'),
            'banco_datos' => CmsBancoDatosResource::collection($this->whenLoaded('bancoDatos')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
