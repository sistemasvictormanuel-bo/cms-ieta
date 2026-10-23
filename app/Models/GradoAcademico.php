<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradoAcademico extends Model
{
    protected $table = 'grado_academicos';

    protected $fillable = [
        'ga_nombre',
        'ga_abreviatura',
        'ga_descripcion',
    ];

    public function autores(): HasMany
    {
        return $this->hasMany(Autor::class, 'grado_academico_id');
    }
}
