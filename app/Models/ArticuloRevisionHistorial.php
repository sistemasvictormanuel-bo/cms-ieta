<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticuloRevisionHistorial extends Model
{
    protected $table = 'articulo_revision_historial';

    protected $fillable = [
        'articulo_id',
        'user_id',
        'accion',
        'estado_anterior',
        'nuevo_estado',
        'motivo',
    ];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
