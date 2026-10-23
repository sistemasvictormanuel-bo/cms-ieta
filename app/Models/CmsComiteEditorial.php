<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsComiteEditorial extends Model
{
    protected $table = 'cms_comite_editoriales';

    protected $fillable = [
        'persona_id',
        'cargo_id',
        'institucion_id',
        'ce_tipo',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(CargoInstitucional::class, 'cargo_id');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Instituciones::class, 'institucion_id');
    }
}
