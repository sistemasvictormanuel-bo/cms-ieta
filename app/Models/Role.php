<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * Determine if this is a system-protected role that cannot be deleted or renamed.
     */
    public function isProtected(): bool
    {
        return in_array(strtolower($this->name), ['administrador', 'autor'], true);
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        $guard = $this->guard_name ?? ($this->attributes['guard_name'] ?? 'web');
        $userModel = getModelForGuard($guard)
            ?? config('auth.providers.users.model')
            ?? User::class;

        return $this->morphedByMany(
            $userModel,
            'model',
            config('permission.table_names.model_has_roles', 'model_has_roles'),
            app(PermissionRegistrar::class)->pivotRole,
            config('permission.column_names.model_morph_key', 'model_id')
        );
    }
}
