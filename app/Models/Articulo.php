<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Articulo extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'articulos';

    protected $fillable = [
        'tipo_articulo_id',
        'edicion_varianza_id',
        'art_titulo',
        'art_slug',
        'art_resumen',
        'art_abstracto',
        'art_fecha_recepcion',
        'art_fecha_aceptado',
        'art_orcid',
        'art_doi',
        'art_num_paginas',
        'art_pag_inicio',
        'art_pag_fin',
        'art_estado',
        'estado_revision',
        'revision_motivo',
        'revisado_por_user_id',
        'revisado_at',
        'created_by_user_id',
        'art_referencias',
        'art_vistas',
    ];

    protected $appends = [
        'archivo_url',
        'etiquetas_list',
    ];

    /**
     * Boot the model. Ensure art_slug is generated if empty.
     */
    protected static function booted(): void
    {
        static::saving(function (Articulo $articulo) {
            if (empty($articulo->art_slug) && ! empty($articulo->art_titulo)) {
                $baseSlug = Str::slug($articulo->art_titulo);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('art_slug', $slug)->where('id', '!=', $articulo->id ?? 0)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }
                $articulo->art_slug = $slug;
            }
        });
    }

    /**
     * Accesor para obtener los nombres de las etiquetas en un array plano.
     */
    public function getEtiquetasListAttribute(): array
    {
        if ($this->relationLoaded('etiquetas')) {
            return $this->etiquetas->pluck('ea_nombre')->toArray();
        }

        return $this->etiquetas()->pluck('ea_nombre')->toArray();
    }

    protected function casts(): array
    {
        return [
            'tipo_articulo_id' => 'integer',
            'edicion_varianza_id' => 'integer',
            'art_fecha_recepcion' => 'date',
            'art_fecha_aceptado' => 'date',
            'art_estado' => 'boolean',
            'art_num_paginas' => 'integer',
            'art_pag_inicio' => 'integer',
            'art_pag_fin' => 'integer',
            'art_vistas' => 'integer',
            'revisado_at' => 'datetime',
        ];
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_user_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function historialRevisiones(): HasMany
    {
        return $this->hasMany(ArticuloRevisionHistorial::class, 'articulo_id')->latest();
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado_revision', 'aprobado')->where('art_estado', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_revision', 'pendiente');
    }

    public function scopeParaAutor($query, $autor)
    {
        $autorId = $autor instanceof Autor ? $autor->id : $autor;

        return $query->whereHas('autores', function ($q) use ($autorId) {
            $q->where('cms_autores.id', $autorId);
        });
    }

    public function getArchivoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('art_archivo');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    /**
     * Tipo de artículo (Artículo Original, Nota Científica, etc.).
     */
    public function tipoArticulo(): BelongsTo
    {
        return $this->belongsTo(TipoArticulo::class, 'tipo_articulo_id');
    }

    /**
     * Edición de Revista Varianza a la que pertenece este artículo (1:N).
     */
    public function edicion(): BelongsTo
    {
        return $this->belongsTo(EdicionVarianza::class, 'edicion_varianza_id');
    }

    /**
     * Ediciones vinculadas mediante relación N:M.
     */
    public function edicionesPivot(): BelongsToMany
    {
        return $this->belongsToMany(
            EdicionVarianza::class,
            'articulos_ediciones_varianza',
            'articulo_id',
            'edicion_varianza_id'
        )
            ->withPivot(['aev_orden', 'aev_pag_inicio', 'aev_pag_fin'])
            ->withTimestamps();
    }

    /**
     * Autores vinculados al artículo.
     */
    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(Autor::class, 'articulo_autor', 'articulo_id', 'autor_id')
            ->withPivot('tipo_participaciones_id')
            ->withTimestamps();
    }

    /**
     * Etiquetas temáticas normalizadas (N:M).
     */
    public function etiquetas(): BelongsToMany
    {
        return $this->belongsToMany(
            EtiquetaArt::class,
            'articulo_etiqueta',
            'articulo_id',
            'etiqueta_id'
        )->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('art_archivo')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->singleFile();
    }
}
