<?php

namespace App\Services;

use App\Models\Instituciones;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class InstitucionesService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Instituciones::withCount('comiteEditorial')->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('inst_nombre', 'like', "%{$term}%")
                    ->orWhere('inst_pais', 'like', "%{$term}%")
                    ->orWhere('inst_ciudad', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return Instituciones::orderBy('inst_nombre')->get();
    }

    public function obtenerPorId(int $id): Instituciones
    {
        return Instituciones::with('comiteEditorial.persona')->findOrFail($id);
    }

    public function guardar(array $data): Instituciones
    {
        try {
            DB::beginTransaction();

            $registro = Instituciones::create([
                'inst_nombre' => $data['inst_nombre'],
                'inst_pais' => $data['inst_pais'],
                'inst_ciudad' => $data['inst_ciudad'],
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): Instituciones
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->update([
                'inst_nombre' => $data['inst_nombre'] ?? $registro->inst_nombre,
                'inst_pais' => $data['inst_pais'] ?? $registro->inst_pais,
                'inst_ciudad' => $data['inst_ciudad'] ?? $registro->inst_ciudad,
            ]);

            DB::commit();

            return $registro;
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
