<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsCursoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rawUrl = $this->getFirstMediaUrl('portada_curso');
        $rutaRelativa = $rawUrl ? parse_url($rawUrl, PHP_URL_PATH) : null;

        return [
            'id' => $this->id,
            'titulo' => $this->ct_titulo,
            'tipo_curso_id' => $this->tipo_curso_id,
            'modalidad_curso_id' => $this->modalidad_curso_id,
            'docente_id' => $this->docente_id,
            'tipo_curso' => new TipoCursoResource($this->whenLoaded('tipoCurso')),
            'modalidad_curso' => new ModalidadCursoResource($this->whenLoaded('modalidadCurso')),
            'docente' => $this->whenLoaded('docente'),
            'fecha_inicio' => $this->ct_fecha_inicio?->format('Y-m-d') ?? $this->ct_fecha_inicio,
            'fecha_fin' => $this->ct_fecha_fin?->format('Y-m-d') ?? $this->ct_fecha_fin,
            'hora_inicio' => $this->ct_hora_inicio,
            'hora_fin' => $this->ct_hora_fin,
            'duracion' => $this->ct_duracion,
            'descripcion' => $this->ct_descripcion,
            'contenido' => $this->ct_contenido,
            'email_info' => $this->ct_email_info,
            'telefono_ref' => $this->ct_telefono_ref,
            'slug' => $this->ct_slug,
            'activo' => (bool) $this->ct_activo,
            'costo' => $this->ct_costo,
            'portada_url' => $rutaRelativa,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
