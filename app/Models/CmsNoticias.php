<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsNoticias extends Model implements HasMedia
{
    use InteractsWithMedia;
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_noticias';

    protected $fillable = [
        'persona_id',
        'not_titulo',
        'not_descripcion',
        'not_fecha',
        'not_destacado',
        'not_publicado',
        'not_contenido',
        'not_url',
    ];

    protected $appends = [
        'portada_url',
    ];

    protected function casts(): array
    {
        return [
            'not_destacado' => 'boolean',
            'not_publicado' => 'boolean',
            'not_fecha' => 'date',
            'not_contenido' => 'array',
        ];
    }

    /**
     * Retorna la ruta relativa de la portada gestionada por Spatie Media Library.
     */
    public function getPortadaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('not_portada');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    /**
     * Normaliza los bloques de Editor.js para garantizar compatibilidad total
     * con @editorjs/list v2 e imágenes relativas.
     */
    public function getNotContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    /**
     * Relación con la Persona (autor/redactor registrado) que redactó la noticia.
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    /**
     * Colección de Spatie Media Library para la imagen de portada.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('not_portada')
            ->singleFile();
    }
}
