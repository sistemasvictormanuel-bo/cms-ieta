<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsRevistaVarianzaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->rv_titulo,
            'subtitulo' => $this->rv_subtitulo,
            'mision' => $this->rv_mision,
            'vision' => $this->rv_vision,
            'contenido' => $this->rv_contenido,
            'alcance' => $this->rv_alcance,
            'politicas' => $this->rv_politicas,
            'issn_impreso' => $this->rv_issn_impreso,
            'issn_electronico' => $this->rv_issn_electronico,
            'periodicidad' => $this->rv_periodicidad,
            'correo_contacto' => $this->rv_correo_contacto,
            'portada_url' => $this->portada_url,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
