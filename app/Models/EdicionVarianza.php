<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EdicionVarianza extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use NormalizesEditorJsBlocks;

    protected $table = 'ediciones_varianzas';

    protected $fillable = [
        'ev_titulo',
        'ev_numero',
        'ev_volumen',
        'ev_anio',
        'ev_mes',
        'ev_slug',
        'ev_issn_ver_impresa',
        'ev_issn_ver_electronica',
        'ev_num_art_originales',
        'ev_notas_cientificas',
        'ev_num_paginas',
        'ev_doi',
        'ev_presentacion',
        'ev_editorial',
        'ev_fecha_publicacion',
        'ev_estado',
    ];

    protected $appends = [
        'portada_url',
        'archivo_url',
    ];

    protected function casts(): array
    {
        return [
            'ev_anio' => 'integer',
            'ev_num_art_originales' => 'integer',
            'ev_notas_cientificas' => 'integer',
            'ev_num_paginas' => 'integer',
            'ev_fecha_publicacion' => 'date',
            'ev_estado' => 'boolean',
            'ev_presentacion' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (EdicionVarianza $edicion) {
            if (empty($edicion->ev_slug) && ! empty($edicion->ev_titulo)) {
                $baseSlug = Str::slug($edicion->ev_titulo);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('ev_slug', $slug)->where('id', '!=', $edicion->id ?? 0)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }
                $edicion->ev_slug = $slug;
            }
        });
    }

    public function getEvPresentacionAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function getPortadaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('ev_portada');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function getArchivoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('ev_archivo');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    /**
     * Artículos vinculados a esta edición (1:N).
     */
    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'edicion_varianza_id')
            ->orderBy('art_pag_inicio')
            ->latest('id');
    }

    /**
     * Relación muchos a muchos con artículos mediante tabla pivote.
     */
    public function articulosPivot(): BelongsToMany
    {
        return $this->belongsToMany(
            Articulo::class,
            'articulos_ediciones_varianza',
            'edicion_varianza_id',
            'articulo_id'
        )
            ->withPivot(['aev_orden', 'aev_pag_inicio', 'aev_pag_fin'])
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ev_portada')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('ev_archivo')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->singleFile();
    }
}
