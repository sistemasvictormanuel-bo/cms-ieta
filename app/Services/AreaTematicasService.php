<?php

namespace App\Services;

use App\Models\AreaTematicas;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class AreaTematicasService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = AreaTematicas::withCount('bancoDatos')->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('at_nombre', 'like', "%{$term}%")
                    ->orWhere('at_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return AreaTematicas::orderBy('at_nombre')->get();
    }

    public function obtenerPorId(int $id): AreaTematicas
    {
        return AreaTematicas::with('bancoDatos')->findOrFail($id);
    }

    public function guardar(array $data): AreaTematicas
    {
        try {
            DB::beginTransaction();

            $registro = AreaTematicas::create([
                'at_nombre' => $data['at_nombre'],
                'at_descripcion' => $data['at_descripcion'] ?? null,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): AreaTematicas
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->update([
                'at_nombre' => $data['at_nombre'] ?? $registro->at_nombre,
                'at_descripcion' => array_key_exists('at_descripcion', $data) ? $data['at_descripcion'] : $registro->at_descripcion,
            ]);

            DB::commit();

            return $registro->fresh('bancoDatos');
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

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
