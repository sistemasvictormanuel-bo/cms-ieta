<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsContactoResource extends JsonResource
{
    /**
     * Transforma el recurso a un arreglo JSON para la API REST.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'telefonos' => $this->cc_telefono,
            'dia_inicio' => $this->cc_dia_inicio,
            'dia_fin' => $this->cc_dia_fin,
            'horario_atencion' => [
                'dia_inicio' => $this->cc_dia_inicio,
                'dia_fin' => $this->cc_dia_fin,
                'hora_apertura' => $this->cc_hora_apertura,
                'hora_cierre' => $this->cc_hora_cierre,
            ],
            'hora_apertura' => $this->cc_hora_apertura,
            'hora_cierre' => $this->cc_hora_cierre,
            'direccion' => $this->cc_direccion,
            'emails' => $this->cc_email,
            'contenido' => $this->cc_contenido,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
