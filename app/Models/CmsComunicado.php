<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsComunicado extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'cms_comunicados';

    protected $fillable = [
        'com_titulo',
        'com_descripcion',
        'com_enlace_url',
        'com_texto_boton',
        'com_tipo',
        'com_fecha_inicio',
        'com_fecha_fin',
        'com_activo',
        'com_vistas',
    ];

    protected $appends = [
        'flyer_url',
    ];

    protected function casts(): array
    {
        return [
            'com_fecha_inicio' => 'date',
            'com_fecha_fin' => 'date',
            'com_activo' => 'boolean',
            'com_vistas' => 'integer',
        ];
    }

    public function getFlyerUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('com_flyer');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    /**
     * Scope para obtener sólo comunicados activos y vigentes a la fecha actual.
     */
    public function scopeVigente(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('com_activo', true)
            ->whereDate('com_fecha_inicio', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('com_fecha_fin')
                    ->orWhereDate('com_fecha_fin', '>=', $today);
            });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('com_flyer')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->singleFile();
    }
}
