<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'modulo',
        'description',
    ];

    /**
     * Determine if this is a system-protected permission that cannot be deleted.
     */
    public function isProtected(): bool
    {
        return in_array($this->name, [
            'usuarios.gestionar',
            'roles.gestionar',
            'permisos.gestionar',
            'articulos.crear',
            'articulos.gestionar-todos',
            'cms.gestionar',
            'perfil.editar-propio',
        ], true);
    }

    /**
     * Scope query to a specific module.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $modulo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }
}
