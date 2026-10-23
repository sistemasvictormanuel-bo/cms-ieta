<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCurso extends Model
{
    protected $table = 'tipo_cursos';

    protected $fillable = ['tc_nombre', 'tc_descripcion'];

    public function cursos(): HasMany
    {
        return $this->hasMany(CmsCursos::class, 'tipo_curso_id');
    }
}
