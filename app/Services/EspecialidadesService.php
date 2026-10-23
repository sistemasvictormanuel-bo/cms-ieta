<?php

namespace App\Services;

use App\Models\Especialidades;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class EspecialidadesService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Especialidades::query()->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ep_nombre', 'like', "%{$term}%")
                    ->orWhere('ep_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return Especialidades::where('ep_estado', true)->orderBy('ep_nombre')->get();
    }

    public function obtenerPorId(int $id): Especialidades
    {
        return Especialidades::findOrFail($id);
    }

    public function guardar(array $data): Especialidades
    {
        try {
            DB::beginTransaction();

            $registro = Especialidades::create([
                'ep_nombre' => $data['ep_nombre'],
                'ep_descripcion' => $data['ep_descripcion'] ?? null,
                'ep_estado' => isset($data['ep_estado']) ? (bool) $data['ep_estado'] : true,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): Especialidades
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'ep_nombre' => $data['ep_nombre'] ?? $registro->ep_nombre,
                'ep_descripcion' => array_key_exists('ep_descripcion', $data) ? $data['ep_descripcion'] : $registro->ep_descripcion,
                'ep_estado' => isset($data['ep_estado']) ? (bool) $data['ep_estado'] : $registro->ep_estado,
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
