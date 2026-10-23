<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsConfiguracion extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'cms_configuracion';

    protected $fillable = [
        'cfg_nombre_institucion',
        'cfg_siglas',
        'cfg_slogan',
        'cfg_descripcion_corta',
        'cfg_email_principal',
        'cfg_telefono_principal',
        'cfg_direccion_principal',
        'cfg_redes_sociales',
        'cfg_copyright',
    ];

    protected $appends = [
        'logo_principal_url',
        'logo_blanco_url',
        'favicon_url',
    ];

    protected function casts(): array
    {
        return [
            'cfg_redes_sociales' => 'array',
        ];
    }

    public function getLogoPrincipalUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('cfg_logo_principal');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function getLogoBlancoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('cfg_logo_blanco');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('cfg_favicon');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cfg_logo_principal')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->singleFile();

        $this->addMediaCollection('cfg_logo_blanco')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->singleFile();

        $this->addMediaCollection('cfg_favicon')
            ->acceptsMimeTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
            ->singleFile();
    }
}
