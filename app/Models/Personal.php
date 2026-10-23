<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personal extends Model
{
    use SoftDeletes;

    protected $table = 'personal';

    protected $fillable = [
        'persona_id',
        'cargo_id',
        'pers_descripcion',
        'pers_fecha_ingreso',
        'pers_fecha_retiro',
        'pers_estado',
    ];

    protected function casts(): array
    {
        return [
            'pers_fecha_ingreso' => 'date',
            'pers_fecha_retiro' => 'date',
            'pers_estado' => 'boolean',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(CargoInstitucional::class, 'cargo_id');
    }
}
