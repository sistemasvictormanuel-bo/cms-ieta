<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AreaTematicas extends Model
{
    protected $table = 'area_tematicas';

    protected $fillable = [
        'at_nombre',
        'at_descripcion',
    ];

    public function bancoDatos(): BelongsToMany
    {
        return $this->belongsToMany(CmsBancoDatos::class, 'banco_datos_tematicas', 'area_id', 'banco_id');
    }
}
