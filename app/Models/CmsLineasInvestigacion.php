<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsLineasInvestigacion extends Model implements HasMedia
{
    use InteractsWithMedia;
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_lineas_investigaciones';

    protected $fillable = [
        'li_titulo',
        'li_descripcion',
        'li_contenido',
        'li_slug',
    ];

    // Adjunta automáticamente 'portada_url' en el JSON/Array al enviarlo a Inertia
    protected $appends = [
        'portada_url',
    ];

    protected function casts(): array
    {
        return [
            'li_contenido' => 'array',
        ];
    }

    // Accessor para que Vue reciba la ruta relativa de la portada directamente sin acoplarse a localhost
    public function getPortadaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('li_imagen');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function getLiContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('li_imagen')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
