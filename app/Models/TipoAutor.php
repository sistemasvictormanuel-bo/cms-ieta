<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoAutor extends Model
{
    protected $table = 'tipo_autores';

    protected $fillable = [
        'ta_nombre',
        'ta_descripcion',
        'ta_estado',
    ];

    protected function casts(): array
    {
        return [
            'ta_estado' => 'boolean',
        ];
    }

    public function autores(): HasMany
    {
        return $this->hasMany(Autor::class, 'tipo_autor_id');
    }
}
