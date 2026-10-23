<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rawUrl = $this->persona?->getFirstMediaUrl('foto_perfil');
        $rutaRelativa = $rawUrl ? parse_url($rawUrl, PHP_URL_PATH) : null;

        return [
            'id' => $this->id,
            'persona_id' => $this->persona_id,
            'p_nombre' => $this->persona?->p_nombre,
            'p_apellido_paterno' => $this->persona?->p_apellido_paterno,
            'p_apellido_materno' => $this->persona?->p_apellido_materno,
            'persona' => $this->whenLoaded('persona'),
            'foto_perfil' => $rutaRelativa ?: $this->persona?->foto_url,
            'email' => $this->persona?->p_correo,
            'tipo_autor_id' => $this->tipo_autor_id,
            'especialidad_id' => $this->especialidad_id,
            'grado_academico_id' => $this->grado_academico_id,
            'biografia' => $this->at_biografia,
            'slug' => $this->at_slug,
            'intereses_cientificos' => $this->at_intereses_cientificos,
            'estado' => (bool) $this->at_estado,
            'tipo_autor' => new TipoAutorResource($this->whenLoaded('tipoAutor')),
            'especialidades' => EspecialidadResource::collection($this->whenLoaded('especialidades')),
            'especialidad' => new EspecialidadResource($this->whenLoaded('especialidad') ?? $this->especialidades?->first()),
            'grado_academico' => new GradoAcademicoResource($this->whenLoaded('gradoAcademico')),
            'articulos' => ArticuloResource::collection($this->whenLoaded('articulos')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
