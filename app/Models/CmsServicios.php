<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsServicios extends Model implements HasMedia
{
    use InteractsWithMedia;
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_servicios';

    protected $fillable = [
        'sv_titulo',
        'sv_descripcion',
        'sv_contenido',
    ];

    /**
     * Adjunta automáticamente 'imagen_url' al serializar el modelo hacia Inertia / JSON.
     */
    protected $appends = [
        'imagen_url',
    ];

    protected function casts(): array
    {
        return [
            'sv_contenido' => 'array',
        ];
    }

    /**
     * Accessor para obtener la ruta relativa de la imagen gestionada por Spatie Media Library.
     */
    public function getImagenUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('sv_imagen');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    /**
     * Normaliza los bloques de Editor.js garantizando compatibilidad total
     * con @editorjs/list v2 mediante el trait NormalizesEditorJsBlocks.
     */
    public function getSvContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    /**
     * Colección de Spatie Media Library para la imagen del servicio.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('sv_imagen')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
