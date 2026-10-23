<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsRevistaVarianza extends Model implements HasMedia
{
    use InteractsWithMedia;
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_revista_varianzas';

    protected $fillable = [
        'rv_titulo',
        'rv_subtitulo',
        'rv_mision',
        'rv_vision',
        'rv_contenido',
        'rv_alcance',
        'rv_politicas',
        'rv_issn_impreso',
        'rv_issn_electronico',
        'rv_periodicidad',
        'rv_correo_contacto',
    ];

    protected $appends = [
        'portada_url',
    ];

    protected function casts(): array
    {
        return [
            'rv_contenido' => 'array',
            'rv_alcance' => 'array',
            'rv_politicas' => 'array',
        ];
    }

    public function getRvContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function getRvAlcanceAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function getRvPoliticasAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function getPortadaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('rv_portada');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('rv_portada')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->singleFile();
    }
}
