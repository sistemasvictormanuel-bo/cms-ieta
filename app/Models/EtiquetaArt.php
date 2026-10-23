<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class EtiquetaArt extends Model
{
    use HasFactory;

    protected $table = 'etiquetas_art';

    protected $fillable = [
        'ea_nombre',
        'ea_slug',
    ];

    /**
     * Boot the model. Automatically ensure slug is generated if missing.
     */
    protected static function booted(): void
    {
        static::saving(function (EtiquetaArt $etiqueta) {
            if (empty($etiqueta->ea_slug) && ! empty($etiqueta->ea_nombre)) {
                $etiqueta->ea_slug = Str::slug($etiqueta->ea_nombre);
            }
        });
    }

    /**
     * Artículos científicos vinculados a esta etiqueta.
     */
    public function articulos(): BelongsToMany
    {
        return $this->belongsToMany(
            Articulo::class,
            'articulo_etiqueta',
            'etiqueta_id',
            'articulo_id'
        )->withTimestamps();
    }
}
