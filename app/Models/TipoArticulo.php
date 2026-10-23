<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoArticulo extends Model
{
    use HasFactory;

    protected $table = 'tipo_articulos';

    protected $fillable = [
        'tar_nombre',
        'tar_descripcion',
        'tar_estado',
    ];

    protected function casts(): array
    {
        return [
            'tar_estado' => 'boolean',
        ];
    }

    /**
     * Artículos clasificados bajo este tipo de publicación.
     */
    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'tipo_articulo_id');
    }
}
