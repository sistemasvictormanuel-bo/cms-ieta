<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargoInstitucional extends Model
{
    protected $table = 'cargo_institucionales';

    protected $fillable = [
        'ci_nombre',
        'ci_descripcion',
    ];

    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class, 'cargo_id');
    }

    public function comiteEditorial(): HasMany
    {
        return $this->hasMany(CmsComiteEditorial::class, 'cargo_id');
    }
}
