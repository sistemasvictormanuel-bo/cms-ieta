<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CargoInstitucionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->ci_nombre,
            'descripcion' => $this->ci_descripcion,
            'personal_count' => $this->whenCounted('personal'),
            'comite_editorial_count' => $this->whenCounted('comiteEditorial'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
