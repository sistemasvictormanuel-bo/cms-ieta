<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MemoriasInvestigacion extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'memorias_investigaciones';

    protected $fillable = [
        'autor_id',
        'mdi_titulo',
        'mdi_descripcion',
        'mdi_etiqueta',
        'mdi_fecha',
        'mdi_enlace_url',
    ];

    protected $appends = [
        'archivo_url',
    ];

    protected function casts(): array
    {
        return [
            'mdi_fecha' => 'date',
        ];
    }

    public function getArchivoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('mdi_archivo');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Autor::class, 'autor_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('mdi_archivo')
            ->singleFile();
    }
}
