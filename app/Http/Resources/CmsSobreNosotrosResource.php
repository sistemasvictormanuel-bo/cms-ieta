<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsSobreNosotrosResource extends JsonResource
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
            'mision' => $this->sn_mision,
            'vision' => $this->sn_vision,
            'historia' => $this->sn_historia,
            'objetivos' => $this->sn_objetivos,
            'contenido' => $this->sn_contenido,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
