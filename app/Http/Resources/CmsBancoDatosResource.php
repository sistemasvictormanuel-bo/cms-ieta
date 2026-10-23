<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsBancoDatosResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_dato' => $this->bdd_tipo_dato,
            'anio' => $this->bdd_anio,
            'codigo' => $this->bdd_codigo,
            'titulo' => $this->bdd_titulo,
            'descripcion' => $this->bdd_descripcion,
            'locacion' => $this->bdd_locacion,
            'url' => $this->bdd_url,
            'bdd_url' => $this->bdd_url,
            'archivo_url' => $this->archivo_url,
            'portada_url' => $this->portada_url,
            'areas_tematicas' => AreaTematicaResource::collection($this->whenLoaded('areasTematicas')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
