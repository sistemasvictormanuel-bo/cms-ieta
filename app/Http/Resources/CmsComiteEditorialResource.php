<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsComiteEditorialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'persona_id' => $this->persona_id,
            'cargo_id' => $this->cargo_id,
            'institucion_id' => $this->institucion_id,
            'tipo' => $this->ce_tipo,
            'persona' => $this->whenLoaded('persona'),
            'cargo' => new CargoInstitucionalResource($this->whenLoaded('cargo')),
            'institucion' => new InstitucionResource($this->whenLoaded('institucion')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
