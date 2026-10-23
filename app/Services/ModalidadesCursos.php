<?php

namespace App\Services;

use App\Models\CursoModalidades;
use Illuminate\Support\Facades\DB;
use Throwable;

class ModalidadesCursos
{
    /**
     * Obtiene todas las modalidades con paginación y búsqueda opcional.
     */
    public function obtenerTodos(?string $search = null, int $perPage = 10)
    {
        $query = CursoModalidades::query()->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('mc_nombre', 'like', "%{$term}%")
                    ->orWhere('mc_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtiene listado simple para selects.
     */
    public function obtenerListado()
    {
        return CursoModalidades::select('id', 'mc_nombre')->orderBy('mc_nombre')->get();
    }

    /**
     * Obtiene un registro por su ID o lanza una excepción.
     */
    public function obtenerPorId(int $id): CursoModalidades
    {
        return CursoModalidades::findOrFail($id);
    }

    /**
     * Guarda una nueva modalidad de curso.
     */
    public function guardar(array $data): CursoModalidades
    {
        try {
            DB::beginTransaction();

            $registro = CursoModalidades::create([
                'mc_nombre' => $data['mc_nombre'] ?? null,
                'mc_descripcion' => $data['mc_descripcion'] ?? null,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una modalidad de curso existente.
     */
    public function actualizar(int $id, array $data): CursoModalidades
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'mc_nombre' => $data['mc_nombre'] ?? $registro->mc_nombre,
                'mc_descripcion' => array_key_exists('mc_descripcion', $data)
                    ? $data['mc_descripcion']
                    : $registro->mc_descripcion,
            ]);

            DB::commit();

            return $registro->fresh();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una modalidad de curso.
     */
    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
