<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Especialidades extends Model
{
    protected $table = 'especialidades';

    protected $fillable = [
        'ep_nombre',
        'ep_descripcion',
        'ep_estado',
    ];

    protected function casts(): array
    {
        return [
            'ep_estado' => 'boolean',
        ];
    }

    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(Autor::class, 'cms_autor_especialidad', 'especialidad_id', 'autor_id')
            ->withTimestamps();
    }
}
