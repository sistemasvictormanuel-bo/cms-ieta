<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Persona extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $table = 'personas';

    protected $fillable = [
        'p_nombre',
        'p_apellido_paterno',
        'p_apellido_materno',
        'p_celular',
        'p_correo',
        'p_sitio_web',
        'user_id',
    ];

    protected $appends = [
        'nombre_completo',
        'foto_url',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto_perfil')->singleFile();
    }

    public function getFotoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('foto_perfil');

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->p_nombre} {$this->p_apellido_paterno} ".($this->p_apellido_materno ?? ''));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function autor(): HasOne
    {
        return $this->hasOne(Autor::class, 'persona_id');
    }

    public function personal(): HasOne
    {
        return $this->hasOne(Personal::class, 'persona_id');
    }

    public function comitesEditoriales(): HasMany
    {
        return $this->hasMany(CmsComiteEditorial::class, 'persona_id');
    }

    public function noticias(): HasMany
    {
        return $this->hasMany(CmsNoticias::class, 'persona_id');
    }
}
