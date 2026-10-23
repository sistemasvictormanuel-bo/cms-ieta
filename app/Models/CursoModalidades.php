<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CursoModalidades extends Model
{
    protected $table = 'curso_modalidades';

    protected $fillable = [
        'mc_nombre',
        'mc_descripcion',
    ];

    public function cursos(): HasMany
    {
        return $this->hasMany(CmsCursos::class, 'modalidad_curso_id');
    }
}
