<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsServicioResource extends JsonResource
{
    /**
     * Transforma el recurso a un arreglo JSON para la API REST.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rawUrl = $this->getFirstMediaUrl('sv_imagen');
        $rutaRelativa = $rawUrl ? parse_url($rawUrl, PHP_URL_PATH) : null;

        return [
            'id' => $this->id,
            'titulo' => $this->sv_titulo,
            'descripcion' => $this->sv_descripcion,
            'contenido' => $this->sv_contenido,
            'imagen_url' => $rutaRelativa,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
