<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsTraduccion extends Model
{
    use HasFactory;

    protected $table = 'cms_traducciones';

    protected $fillable = [
        'modulo',
        'clave',
        'texto_es',
        'texto_en',
        'descripcion',
    ];

    /**
     * Scope por módulo o sección.
     */
    public function scopeModulo(Builder $query, string $modulo): Builder
    {
        return $query->where('modulo', $modulo);
    }

    /**
     * Obtiene el diccionario de traducciones optimizado para la API pública.
     *
     * @param  string|null  $locale  'es', 'en' o null (ambos)
     * @param  string|null  $modulo  Filtrar por módulo específico
     * @return array<string, mixed>
     */
    public static function getDiccionario(?string $locale = null, ?string $modulo = null): array
    {
        $query = static::query();

        if ($modulo !== null && $modulo !== '' && $modulo !== 'todos') {
            $query->where('modulo', $modulo);
        }

        $items = $query->get(['modulo', 'clave', 'texto_es', 'texto_en']);

        $normalizedLocale = $locale ? strtolower(trim($locale)) : null;

        if ($normalizedLocale === 'en') {
            return $items->pluck('texto_en', 'clave')->toArray();
        }

        if ($normalizedLocale === 'es') {
            return $items->pluck('texto_es', 'clave')->toArray();
        }

        // Si no se especifica idioma, devuelve estructura bilingüe
        $resultado = [
            'es' => [],
            'en' => [],
        ];

        foreach ($items as $item) {
            $resultado['es'][$item->clave] = $item->texto_es;
            $resultado['en'][$item->clave] = $item->texto_en;
        }

        return $resultado;
    }
}
