<?php

namespace App\Services;

use App\Models\MemoriasInvestigacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class MemoriasInvestigacionService
{
    public function obtenerTodos(
        ?string $search = null,
        ?int $autorId = null,
        ?string $etiqueta = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = MemoriasInvestigacion::with(['autor.persona', 'media'])->latest('id');

        if ($autorId) {
            $query->where('autor_id', $autorId);
        }

        if ($etiqueta !== null && trim($etiqueta) !== '') {
            $query->where('mdi_etiqueta', trim($etiqueta));
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('mdi_titulo', 'like', "%{$term}%")
                    ->orWhere('mdi_descripcion', 'like', "%{$term}%")
                    ->orWhere('mdi_etiqueta', 'like', "%{$term}%")
                    ->orWhere('mdi_enlace_url', 'like', "%{$term}%")
                    ->orWhereHas('autor.persona', function ($pq) use ($term) {
                        $pq->where('p_nombre', 'like', "%{$term}%")
                            ->orWhere('p_apellido_paterno', 'like', "%{$term}%")
                            ->orWhere('p_apellido_materno', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return MemoriasInvestigacion::with(['autor.persona', 'media'])->orderByDesc('mdi_fecha')->get();
    }

    public function obtenerPorId(int $id): MemoriasInvestigacion
    {
        return MemoriasInvestigacion::with(['autor.persona', 'media'])->findOrFail($id);
    }

    public function guardar(array $data, ?UploadedFile $archivo = null): MemoriasInvestigacion
    {
        try {
            DB::beginTransaction();

            $registro = MemoriasInvestigacion::create([
                'autor_id' => $data['autor_id'],
                'mdi_titulo' => $data['mdi_titulo'],
                'mdi_descripcion' => $data['mdi_descripcion'] ?? null,
                'mdi_etiqueta' => $data['mdi_etiqueta'],
                'mdi_fecha' => $data['mdi_fecha'],
                'mdi_enlace_url' => $data['mdi_enlace_url'] ?? null,
            ]);

            if ($archivo) {
                $registro->addMedia($archivo)->toMediaCollection('mdi_archivo');
            }

            DB::commit();

            return $registro->load(['autor.persona', 'media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data, ?UploadedFile $archivo = null): MemoriasInvestigacion
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'autor_id' => $data['autor_id'] ?? $registro->autor_id,
                'mdi_titulo' => $data['mdi_titulo'] ?? $registro->mdi_titulo,
                'mdi_descripcion' => array_key_exists('mdi_descripcion', $data) ? $data['mdi_descripcion'] : $registro->mdi_descripcion,
                'mdi_etiqueta' => $data['mdi_etiqueta'] ?? $registro->mdi_etiqueta,
                'mdi_fecha' => $data['mdi_fecha'] ?? $registro->mdi_fecha,
                'mdi_enlace_url' => array_key_exists('mdi_enlace_url', $data) ? $data['mdi_enlace_url'] : $registro->mdi_enlace_url,
            ]);

            if ($archivo) {
                $registro->clearMediaCollection('mdi_archivo');
                $registro->addMedia($archivo)->toMediaCollection('mdi_archivo');
            }

            DB::commit();

            return $registro->fresh(['autor.persona', 'media']);
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
            $registro->clearMediaCollection('mdi_archivo');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
