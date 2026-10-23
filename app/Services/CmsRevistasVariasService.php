<?php

namespace App\Services;

use App\Models\CmsRevistasVarias;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsRevistasVariasService
{
    /**
     * Obtiene el listado de revistas de referencia con filtros y paginación.
     */
    public function obtenerTodos(
        ?string $search = null,
        ?bool $activo = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = CmsRevistasVarias::with('media')
            ->orderBy('rev_orden', 'asc')
            ->latest('id');

        if ($activo !== null) {
            $query->where('rev_activo', $activo);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }

                $q->orWhere('rev_titulo', 'like', "%{$term}%")
                    ->orWhere('rev_descripcion', 'like', "%{$term}%")
                    ->orWhere('rev_entidad', 'like', "%{$term}%")
                    ->orWhere('rev_issn', 'like', "%{$term}%")
                    ->orWhere('rev_url_enlace', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtiene una revista referencial por ID.
     */
    public function obtenerPorId(int $id): CmsRevistasVarias
    {
        return CmsRevistasVarias::with('media')->findOrFail($id);
    }

    /**
     * Registra una nueva revista de referencia.
     */
    public function guardar(array $data, ?UploadedFile $portada = null): CmsRevistasVarias
    {
        try {
            DB::beginTransaction();

            $registro = CmsRevistasVarias::create([
                'rev_titulo' => $data['rev_titulo'],
                'rev_descripcion' => $data['rev_descripcion'] ?? null,
                'rev_url_enlace' => $data['rev_url_enlace'],
                'rev_entidad' => $data['rev_entidad'] ?? null,
                'rev_issn' => $data['rev_issn'] ?? null,
                'rev_activo' => isset($data['rev_activo']) ? (bool) $data['rev_activo'] : true,
                'rev_orden' => (int) ($data['rev_orden'] ?? 0),
            ]);

            if ($portada) {
                $registro->addMedia($portada)->toMediaCollection('rev_portada');
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una revista de referencia.
     */
    public function actualizar(int $id, array $data, ?UploadedFile $portada = null): CmsRevistasVarias
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'rev_titulo' => $data['rev_titulo'] ?? $registro->rev_titulo,
                'rev_descripcion' => $data['rev_descripcion'] ?? $registro->rev_descripcion,
                'rev_url_enlace' => $data['rev_url_enlace'] ?? $registro->rev_url_enlace,
                'rev_entidad' => $data['rev_entidad'] ?? $registro->rev_entidad,
                'rev_issn' => $data['rev_issn'] ?? $registro->rev_issn,
                'rev_activo' => isset($data['rev_activo']) ? (bool) $data['rev_activo'] : $registro->rev_activo,
                'rev_orden' => isset($data['rev_orden']) ? (int) $data['rev_orden'] : $registro->rev_orden,
            ]);

            if ($portada) {
                $registro->addMedia($portada)->toMediaCollection('rev_portada');
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una revista de referencia.
     */
    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->clearMediaCollection('rev_portada');
            $resultado = $registro->delete();

            DB::commit();

            return (bool) $resultado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Alterna la visibilidad pública de la revista.
     */
    public function toggleActivo(int $id): CmsRevistasVarias
    {
        $registro = $this->obtenerPorId($id);
        $registro->rev_activo = ! $registro->rev_activo;
        $registro->save();

        return $registro;
    }

    /**
     * Obtiene el listado de revistas activas para la API pública.
     */
    public function obtenerParaApi(?int $limit = null): Collection
    {
        $query = CmsRevistasVarias::with('media')
            ->where('rev_activo', true)
            ->orderBy('rev_orden', 'asc')
            ->latest('id');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
