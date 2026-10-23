<?php

namespace App\Policies;

use App\Models\Articulo;
use App\Models\User;

class ArticuloPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('administrador') || $user->hasRole('autor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Articulo $articulo): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        if ($user->hasRole('autor')) {
            return $this->perteneceAAutor($user, $articulo) || ($articulo->estado_revision === 'aprobado' && $articulo->art_estado);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('administrador') || $user->hasRole('autor');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Articulo $articulo): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        if ($user->hasRole('autor')) {
            return $this->perteneceAAutor($user, $articulo);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Articulo $articulo): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        // Un autor solo podría eliminar su propio artículo si aún está en borrador
        if ($user->hasRole('autor')) {
            return $this->perteneceAAutor($user, $articulo) && $articulo->estado_revision === 'borrador';
        }

        return false;
    }

    /**
     * Determine whether the user can review (approve/reject) the model.
     */
    public function revisar(User $user, Articulo $articulo): bool
    {
        return $user->hasRole('administrador') || $user->can('articulos.revisar');
    }

    /**
     * Comprueba si el artículo pertenece al autor autenticado.
     */
    protected function perteneceAAutor(User $user, Articulo $articulo): bool
    {
        if ($articulo->created_by_user_id === $user->id) {
            return true;
        }

        $autor = $user->autor();
        if ($autor && $articulo->autores()->where('cms_autores.id', $autor->id)->exists()) {
            return true;
        }

        return false;
    }
}
