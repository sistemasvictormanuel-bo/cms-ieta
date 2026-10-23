<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsLineaInvestigacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rawUrl = $this->getFirstMediaUrl('li_imagen');
        $rutaRelativa = $rawUrl ? parse_url($rawUrl, PHP_URL_PATH) : null;

        return [
            'id' => $this->id,
            'titulo' => $this->li_titulo,
            'slug' => $this->li_slug,
            'descripcion' => $this->li_descripcion,
            'contenido' => $this->li_contenido,
            'imagen_url' => $rutaRelativa,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
