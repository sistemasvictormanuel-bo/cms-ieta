<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmsCursos extends Model implements HasMedia
{
    use InteractsWithMedia;
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_cursos';

    protected $fillable = [
        'ct_titulo',
        'tipo_curso_id',
        'modalidad_curso_id',
        'docente_id',
        'ct_fecha_inicio',
        'ct_fecha_fin',
        'ct_hora_inicio',
        'ct_hora_fin',
        'ct_duracion',
        'ct_descripcion',
        'ct_contenido',
        'ct_email_info',
        'ct_telefono_ref',
        'ct_slug',
        'ct_activo',
        'ct_costo',
    ];

    protected $appends = [
        'portada_url',
    ];

    protected function casts(): array
    {
        return [
            'ct_fecha_inicio' => 'date',
            'ct_fecha_fin' => 'date',
            'ct_activo' => 'boolean',
            'ct_costo' => 'decimal:2',
            'ct_contenido' => 'array',
        ];
    }

    public function getPortadaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('portada_curso');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function getCtContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function tipoCurso(): BelongsTo
    {
        return $this->belongsTo(TipoCurso::class, 'tipo_curso_id');
    }

    public function modalidadCurso(): BelongsTo
    {
        return $this->belongsTo(CursoModalidades::class, 'modalidad_curso_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'docente_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portada_curso')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }
}
