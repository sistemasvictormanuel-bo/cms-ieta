<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsGaleria extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'cms_galerias';

    protected $fillable = [
        'gal_titulo',
        'gal_descripcion',
        'gal_categoria',
        'gal_fecha',
        'gal_lugar',
        'gal_destacado',
        'gal_activo',
        'gal_orden',
    ];

    protected $appends = [
        'imagen_url',
    ];

    protected function casts(): array
    {
        return [
            'gal_fecha' => 'date',
            'gal_destacado' => 'boolean',
            'gal_activo' => 'boolean',
            'gal_orden' => 'integer',
        ];
    }

    /**
     * Retorna la ruta relativa o URL pública de la imagen en Spatie Media Library.
     */
    public function getImagenUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('gal_imagen');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    /**
     * Colección multimedia de la galería.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gal_imagen')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/svg+xml', 'image/avif'])
            ->singleFile();
    }
}
