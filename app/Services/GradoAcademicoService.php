<?php

namespace App\Services;

use App\Models\GradoAcademico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class GradoAcademicoService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = GradoAcademico::query()->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ga_nombre', 'like', "%{$term}%")
                    ->orWhere('ga_abreviatura', 'like', "%{$term}%")
                    ->orWhere('ga_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return GradoAcademico::orderBy('ga_nombre')->get();
    }

    public function obtenerPorId(int $id): GradoAcademico
    {
        return GradoAcademico::findOrFail($id);
    }

    public function guardar(array $data): GradoAcademico
    {
        try {
            DB::beginTransaction();

            $registro = GradoAcademico::create([
                'ga_nombre' => $data['ga_nombre'],
                'ga_abreviatura' => $data['ga_abreviatura'],
                'ga_descripcion' => $data['ga_descripcion'] ?? null,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): GradoAcademico
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'ga_nombre' => $data['ga_nombre'] ?? $registro->ga_nombre,
                'ga_abreviatura' => $data['ga_abreviatura'] ?? $registro->ga_abreviatura,
                'ga_descripcion' => array_key_exists('ga_descripcion', $data) ? $data['ga_descripcion'] : $registro->ga_descripcion,
            ]);

            DB::commit();

            return $registro->fresh();
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
