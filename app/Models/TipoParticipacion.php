<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoParticipacion extends Model
{
    protected $table = 'tipo_participaciones';

    protected $fillable = [
        'tp_nombre',
        'tp_descripcion',
    ];
}
