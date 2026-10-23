<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsRevistasVarias extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'cms_revistas_varias';

    protected $fillable = [
        'rev_titulo',
        'rev_descripcion',
        'rev_url_enlace',
        'rev_entidad',
        'rev_issn',
        'rev_activo',
        'rev_orden',
    ];

    protected $appends = [
        'portada_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rev_activo' => 'boolean',
            'rev_orden' => 'integer',
        ];
    }

    /**
     * Accesor para la portada o logo de la revista referencial.
     */
    public function getPortadaUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('rev_portada');
        if (! $media) {
            return null;
        }

        return parse_url($media->getUrl(), PHP_URL_PATH);
    }

    /**
     * Configuración de Media Collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('rev_portada')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/avif',
            ]);
    }
}
