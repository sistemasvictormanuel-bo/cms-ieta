<?php

namespace App\Services;

use App\Models\TipoParticipacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class TipoParticipacionService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = TipoParticipacion::query()->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('tp_nombre', 'like', "%{$term}%")
                    ->orWhere('tp_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return TipoParticipacion::orderBy('tp_nombre')->get();
    }

    public function obtenerPorId(int $id): TipoParticipacion
    {
        return TipoParticipacion::findOrFail($id);
    }

    public function guardar(array $data): TipoParticipacion
    {
        try {
            DB::beginTransaction();

            $registro = TipoParticipacion::create([
                'tp_nombre' => $data['tp_nombre'],
                'tp_descripcion' => $data['tp_descripcion'] ?? null,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): TipoParticipacion
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'tp_nombre' => $data['tp_nombre'] ?? $registro->tp_nombre,
                'tp_descripcion' => array_key_exists('tp_descripcion', $data) ? $data['tp_descripcion'] : $registro->tp_descripcion,
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
