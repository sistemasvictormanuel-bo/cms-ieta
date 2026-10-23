<?php

namespace App\Services;

use App\Models\CargoInstitucional;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class CargoInstitucionalService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = CargoInstitucional::withCount(['personal', 'comiteEditorial'])->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ci_nombre', 'like', "%{$term}%")
                    ->orWhere('ci_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return CargoInstitucional::orderBy('ci_nombre')->get();
    }

    public function obtenerPorId(int $id): CargoInstitucional
    {
        return CargoInstitucional::with(['personal.persona', 'comiteEditorial.persona'])->findOrFail($id);
    }

    public function guardar(array $data): CargoInstitucional
    {
        try {
            DB::beginTransaction();

            $registro = CargoInstitucional::create([
                'ci_nombre' => $data['ci_nombre'],
                'ci_descripcion' => $data['ci_descripcion'] ?? null,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): CargoInstitucional
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->update([
                'ci_nombre' => $data['ci_nombre'] ?? $registro->ci_nombre,
                'ci_descripcion' => array_key_exists('ci_descripcion', $data) ? $data['ci_descripcion'] : $registro->ci_descripcion,
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
