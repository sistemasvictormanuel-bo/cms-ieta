<?php

namespace App\Services;

use App\Models\CmsServicios;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CmsServiciosService
{
    /**
     * Obtiene todos los servicios con paginación y búsqueda por ID, título o descripción.
     */
    public function obtenerTodas(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = CmsServicios::with('media')->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);

            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('sv_titulo', 'like', "%{$term}%")
                    ->orWhere('sv_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtiene un servicio por su identificador único.
     */
    public function obtenerPorId(int $id): CmsServicios
    {
        return CmsServicios::with('media')->findOrFail($id);
    }

    /**
     * Registra un nuevo servicio en la base de datos con su imagen adjunta.
     */
    public function guardar(array $data, ?UploadedFile $imagen = null): CmsServicios
    {
        try {
            DB::beginTransaction();

            $registro = CmsServicios::create([
                'sv_titulo' => $data['sv_titulo'],
                'sv_descripcion' => $data['sv_descripcion'] ?? null,
                'sv_contenido' => $data['sv_contenido'],
            ]);

            if ($imagen) {
                $registro->addMedia($imagen)->toMediaCollection('sv_imagen');
            }

            DB::commit();

            return $registro->load('media');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza la información de un servicio existente y reemplaza la imagen si se envía una nueva.
     */
    public function actualizar(int $id, array $data, ?UploadedFile $imagen = null): CmsServicios
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'sv_titulo' => $data['sv_titulo'] ?? $registro->sv_titulo,
                'sv_descripcion' => array_key_exists('sv_descripcion', $data)
                    ? $data['sv_descripcion']
                    : $registro->sv_descripcion,
                'sv_contenido' => $data['sv_contenido'] ?? $registro->sv_contenido,
            ]);

            if ($imagen) {
                $registro->clearMediaCollection('sv_imagen');
                $registro->addMedia($imagen)->toMediaCollection('sv_imagen');
            }

            DB::commit();

            return $registro->fresh('media');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un servicio y sus recursos multimedia asociados.
     */
    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->clearMediaCollection('sv_imagen');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
