<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsReglamentoInterno extends Model implements HasMedia
{
    use InteractsWithMedia;
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_reglamento_internos';

    protected $fillable = [
        'reg_titulo',
        'reg_descripcion',
        'reg_contenido',
    ];

    protected $appends = [
        'archivo_url',
        'archivo_nombre',
        'archivo_size',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reg_contenido' => 'array',
        ];
    }

    /**
     * Accesor para normalizar bloques de Editor.js.
     */
    public function getRegContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    /**
     * URL del archivo PDF del reglamento.
     */
    public function getArchivoUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('reg_archivo');
        if (! $media) {
            return null;
        }

        return parse_url($media->getUrl(), PHP_URL_PATH);
    }

    /**
     * Nombre original del archivo PDF.
     */
    public function getArchivoNombreAttribute(): ?string
    {
        return $this->getFirstMedia('reg_archivo')?->file_name;
    }

    /**
     * Tamaño en bytes del archivo PDF.
     */
    public function getArchivoSizeAttribute(): ?int
    {
        return $this->getFirstMedia('reg_archivo')?->size;
    }

    /**
     * Configuración de Media Collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('reg_archivo')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'application/x-pdf',
                'application/acrobat',
                'applications/vnd.pdf',
                'text/pdf',
                'text/x-pdf',
            ]);
    }
}
