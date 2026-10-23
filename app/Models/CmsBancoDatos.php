<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsBancoDatos extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'cms_banco_datos';

    protected $fillable = [
        'bdd_tipo_dato',
        'bdd_anio',
        'bdd_codigo',
        'bdd_titulo',
        'bdd_descripcion',
        'bdd_locacion',
        'bdd_url',
    ];

    protected $appends = [
        'archivo_url',
        'portada_url',
    ];

    protected function casts(): array
    {
        return [
            'bdd_anio' => 'integer',
        ];
    }

    public function getArchivoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('bdd_archivo');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function getPortadaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('bdd_portada');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function areasTematicas(): BelongsToMany
    {
        return $this->belongsToMany(AreaTematicas::class, 'banco_datos_tematicas', 'banco_id', 'area_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('bdd_archivo')
            ->singleFile();

        $this->addMediaCollection('bdd_portada')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }
}
