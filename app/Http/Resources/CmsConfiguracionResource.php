<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsConfiguracionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_institucion' => $this->cfg_nombre_institucion,
            'siglas' => $this->cfg_siglas,
            'slogan' => $this->cfg_slogan,
            'descripcion_corta' => $this->cfg_descripcion_corta,
            'email_principal' => $this->cfg_email_principal,
            'telefono_principal' => $this->cfg_telefono_principal,
            'direccion_principal' => $this->cfg_direccion_principal,
            'redes_sociales' => $this->cfg_redes_sociales ?? [
                'facebook' => '',
                'twitter' => '',
                'linkedin' => '',
                'youtube' => '',
                'instagram' => '',
            ],
            'copyright' => $this->cfg_copyright,
            'logo_principal_url' => $this->logo_principal_url,
            'logo_blanco_url' => $this->logo_blanco_url,
            'favicon_url' => $this->favicon_url,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
