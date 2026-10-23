<?php

namespace App\Services;

use App\Models\TipoCurso;
use Illuminate\Support\Facades\DB;
use Throwable;

class TipoCursosService
{
    /**
     * Obtiene todos los tipos de cursos con paginación y búsqueda opcional.
     */
    public function obtenerTodos(?string $search = null, int $perPage = 10)
    {
        $query = TipoCurso::query()->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('tc_nombre', 'like', "%{$term}%")
                    ->orWhere('tc_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtiene listado simple para selects.
     */
    public function obtenerListado()
    {
        return TipoCurso::select('id', 'tc_nombre')->orderBy('tc_nombre')->get();
    }

    /**
     * Obtiene un registro por su ID o lanza una excepción.
     */
    public function obtenerPorId(int $id): TipoCurso
    {
        return TipoCurso::findOrFail($id);
    }

    /**
     * Guarda un nuevo tipo de curso.
     */
    public function guardar(array $data): TipoCurso
    {
        try {
            DB::beginTransaction();

            $registro = TipoCurso::create([
                'tc_nombre' => $data['tc_nombre'] ?? null,
                'tc_descripcion' => $data['tc_descripcion'] ?? null,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un tipo de curso existente.
     */
    public function actualizar(int $id, array $data): TipoCurso
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'tc_nombre' => $data['tc_nombre'] ?? $registro->tc_nombre,
                'tc_descripcion' => array_key_exists('tc_descripcion', $data)
                    ? $data['tc_descripcion']
                    : $registro->tc_descripcion,
            ]);

            DB::commit();

            return $registro->fresh();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un tipo de curso.
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
