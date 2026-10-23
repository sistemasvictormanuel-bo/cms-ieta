<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instituciones extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'inst_nombre',
        'inst_pais',
        'inst_ciudad',
    ];

    public function comiteEditorial(): HasMany
    {
        return $this->hasMany(CmsComiteEditorial::class, 'institucion_id');
    }
}
