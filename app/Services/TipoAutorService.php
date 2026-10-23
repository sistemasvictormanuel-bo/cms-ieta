<?php

namespace App\Services;

use App\Models\TipoAutor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class TipoAutorService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = TipoAutor::query()->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ta_nombre', 'like', "%{$term}%")
                    ->orWhere('ta_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return TipoAutor::where('ta_estado', true)->orderBy('ta_nombre')->get();
    }

    public function obtenerPorId(int $id): TipoAutor
    {
        return TipoAutor::findOrFail($id);
    }

    public function guardar(array $data): TipoAutor
    {
        try {
            DB::beginTransaction();

            $registro = TipoAutor::create([
                'ta_nombre' => $data['ta_nombre'],
                'ta_descripcion' => $data['ta_descripcion'] ?? null,
                'ta_estado' => isset($data['ta_estado']) ? (bool) $data['ta_estado'] : true,
            ]);

            DB::commit();

            return $registro;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): TipoAutor
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'ta_nombre' => $data['ta_nombre'] ?? $registro->ta_nombre,
                'ta_descripcion' => array_key_exists('ta_descripcion', $data) ? $data['ta_descripcion'] : $registro->ta_descripcion,
                'ta_estado' => isset($data['ta_estado']) ? (bool) $data['ta_estado'] : $registro->ta_estado,
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
