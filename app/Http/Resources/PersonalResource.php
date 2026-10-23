<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'persona_id' => $this->persona_id,
            'p_nombre' => $this->persona->p_nombre,
            'p_apellido_paterno' => $this->persona->p_apellido_paterno,
            'p_apellido_materno' => $this->persona->p_apellido_materno,
            'email' => $this->persona->email,
            'cargo_id' => $this->cargo_id,
            'descripcion' => $this->pers_descripcion,
            'fecha_ingreso' => $this->pers_fecha_ingreso?->format('Y-m-d'),
            'fecha_retiro' => $this->pers_fecha_retiro?->format('Y-m-d'),
            'estado' => (bool) $this->pers_estado,
            'persona' => $this->whenLoaded('persona'),
            'cargo' => new CargoInstitucionalResource($this->whenLoaded('cargo')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
