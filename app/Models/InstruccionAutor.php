<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstruccionAutor extends Model
{
    use HasFactory, NormalizesEditorJsBlocks, SoftDeletes;

    protected $table = 'instrucciones_autores';

    protected $fillable = [
        'ia_titulo_seccion',
        'ia_contenido',
        'ia_orden',
        'ia_activo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ia_contenido' => 'array',
            'ia_orden' => 'integer',
            'ia_activo' => 'boolean',
        ];
    }

    /**
     * Normaliza los bloques de Editor.js garantizando compatibilidad con la suite de renderizado.
     */
    public function getIaContenidoAttribute(mixed $value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    /**
     * Scope para secciones activas visibles.
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('ia_activo', true);
    }

    /**
     * Scope para ordenar cronológica/visualmente las secciones de la guía.
     */
    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderBy('ia_orden', 'asc')->orderBy('id', 'asc');
    }
}
