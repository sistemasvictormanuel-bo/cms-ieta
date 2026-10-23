<?php

namespace App\Services;

use App\Models\InstruccionAutor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InstruccionAutorService
{
    /**
     * Obtiene directrices paginadas con soporte de búsqueda y orden por defecto.
     */
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = InstruccionAutor::ordenado();

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ia_titulo_seccion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtiene todas las directrices activas ordenadas para el portal público o API.
     */
    public function obtenerActivos(): Collection
    {
        return InstruccionAutor::activo()->ordenado()->get();
    }

    /**
     * Obtiene una instrucción por su ID.
     */
    public function obtenerPorId(int $id): InstruccionAutor
    {
        return InstruccionAutor::findOrFail($id);
    }

    /**
     * Registra una nueva instrucción para autores.
     */
    public function guardar(array $datos): InstruccionAutor
    {
        if (! isset($datos['ia_orden']) || $datos['ia_orden'] === null || $datos['ia_orden'] === '') {
            $maxOrden = (int) InstruccionAutor::max('ia_orden');
            $datos['ia_orden'] = $maxOrden + 1;
        }

        if (isset($datos['ia_activo'])) {
            $datos['ia_activo'] = (bool) $datos['ia_activo'];
        }

        return InstruccionAutor::create($datos);
    }

    /**
     * Actualiza una directriz existente.
     */
    public function actualizar(int $id, array $datos): InstruccionAutor
    {
        $instruccion = $this->obtenerPorId($id);

        if (isset($datos['ia_activo'])) {
            $datos['ia_activo'] = (bool) $datos['ia_activo'];
        }

        $instruccion->update($datos);

        return $instruccion->fresh();
    }

    /**
     * Elimina lógicamente una directriz.
     */
    public function eliminar(int $id): bool
    {
        $instruccion = $this->obtenerPorId($id);

        return (bool) $instruccion->delete();
    }

    /**
     * Alterna el estado activo / inactivo.
     */
    public function toggleActivo(int $id): InstruccionAutor
    {
        $instruccion = $this->obtenerPorId($id);
        $instruccion->ia_activo = ! $instruccion->ia_activo;
        $instruccion->save();

        return $instruccion;
    }
}
