<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Autor extends Model
{
    use NormalizesEditorJsBlocks;
    use SoftDeletes;

    protected $table = 'cms_autores';

    protected $fillable = [
        'persona_id',
        'tipo_autor_id',
        'grado_academico_id',
        'at_biografia',
        'at_slug',
        'at_intereses_cientificos',
        'at_estado',
    ];

    protected function casts(): array
    {
        return [
            'at_biografia' => 'array',
            'at_estado' => 'boolean',
        ];
    }

    public function getAtBiografiaAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    protected $appends = [
        'especialidad',
    ];

    public function tipoAutor(): BelongsTo
    {
        return $this->belongsTo(TipoAutor::class, 'tipo_autor_id');
    }

    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidades::class, 'cms_autor_especialidad', 'autor_id', 'especialidad_id')
            ->withTimestamps();
    }

    public function especialidad(): BelongsToMany
    {
        return $this->especialidades();
    }

    public function getEspecialidadAttribute(): ?Especialidades
    {
        return $this->relationLoaded('especialidades') ? $this->especialidades->first() : $this->especialidades()->first();
    }

    public function gradoAcademico(): BelongsTo
    {
        return $this->belongsTo(GradoAcademico::class, 'grado_academico_id');
    }

    public function articulos(): BelongsToMany
    {
        return $this->belongsToMany(Articulo::class, 'articulo_autor', 'autor_id', 'articulo_id')
            ->withPivot('tipo_participaciones_id')
            ->withTimestamps();
    }

    public function memoriasInvestigaciones(): HasMany
    {
        return $this->hasMany(MemoriasInvestigacion::class, 'autor_id');
    }
}
