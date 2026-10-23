<?php

namespace App\Services;

use App\Models\CmsNoticias;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsNoticiasService
{
    /**
     * Obtiene todas las noticias paginadas con filtros de búsqueda, estado y autor.
     */
    public function obtenerTodos(
        ?string $search = null,
        ?bool $publicado = null,
        ?bool $destacado = null,
        ?int $personaId = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = CmsNoticias::with(['persona', 'media'])->latest('id');

        if ($personaId !== null) {
            $query->where('persona_id', $personaId);
        }

        if ($publicado !== null) {
            $query->where('not_publicado', $publicado);
        }

        if ($destacado !== null) {
            $query->where('not_destacado', $destacado);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);

            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }

                $q->orWhere('not_titulo', 'like', "%{$term}%")
                    ->orWhere('not_descripcion', 'like', "%{$term}%")
                    ->orWhere('not_url', 'like', "%{$term}%")
                    ->orWhereHas('persona', function ($pq) use ($term) {
                        $pq->where('p_nombre', 'like', "%{$term}%")
                            ->orWhere('p_apellido_paterno', 'like', "%{$term}%")
                            ->orWhere('p_apellido_materno', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtiene el listado de noticias publicadas para el portal web o API.
     */
    public function obtenerListadoPublico(?int $limit = null, ?bool $destacadas = null): Collection
    {
        $query = CmsNoticias::with(['persona', 'media'])
            ->where('not_publicado', true)
            ->orderByDesc('not_fecha')
            ->latest('id');

        if ($destacadas !== null) {
            $query->where('not_destacado', $destacadas);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Obtiene una noticia por su ID con sus relaciones.
     */
    public function obtenerPorId(int $id): CmsNoticias
    {
        return CmsNoticias::with(['persona', 'media'])->findOrFail($id);
    }

    /**
     * Registra una nueva noticia en la base de datos con portada opcional en Spatie.
     */
    public function guardar(array $data, ?UploadedFile $portada = null): CmsNoticias
    {
        try {
            DB::beginTransaction();

            $registro = CmsNoticias::create([
                'persona_id' => $data['persona_id'] ?? null,
                'not_titulo' => $data['not_titulo'],
                'not_descripcion' => $data['not_descripcion'] ?? null,
                'not_fecha' => $data['not_fecha'] ?? now()->toDateString(),
                'not_destacado' => (bool) ($data['not_destacado'] ?? false),
                'not_publicado' => (bool) ($data['not_publicado'] ?? false),
                'not_contenido' => $data['not_contenido'] ?? ['blocks' => []],
                'not_url' => $data['not_url'] ?? null,
            ]);

            if ($portada) {
                $registro->addMedia($portada)->toMediaCollection('not_portada');
            }

            DB::commit();

            return $registro->load(['persona', 'media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una noticia existente con reemplazo opcional de portada.
     */
    public function actualizar(int $id, array $data, ?UploadedFile $portada = null): CmsNoticias
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'persona_id' => array_key_exists('persona_id', $data) ? $data['persona_id'] : $registro->persona_id,
                'not_titulo' => $data['not_titulo'] ?? $registro->not_titulo,
                'not_descripcion' => array_key_exists('not_descripcion', $data) ? $data['not_descripcion'] : $registro->not_descripcion,
                'not_fecha' => array_key_exists('not_fecha', $data) ? $data['not_fecha'] : $registro->not_fecha,
                'not_destacado' => array_key_exists('not_destacado', $data) ? (bool) $data['not_destacado'] : $registro->not_destacado,
                'not_publicado' => array_key_exists('not_publicado', $data) ? (bool) $data['not_publicado'] : $registro->not_publicado,
                'not_contenido' => array_key_exists('not_contenido', $data) ? $data['not_contenido'] : $registro->not_contenido,
                'not_url' => array_key_exists('not_url', $data) ? $data['not_url'] : $registro->not_url,
            ]);

            if ($portada) {
                $registro->clearMediaCollection('not_portada');
                $registro->addMedia($portada)->toMediaCollection('not_portada');
            }

            DB::commit();

            return $registro->fresh(['persona', 'media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Alterna el estado destacado de una noticia.
     */
    public function toggleDestacado(int $id): CmsNoticias
    {
        $noticia = $this->obtenerPorId($id);
        $noticia->update(['not_destacado' => ! $noticia->not_destacado]);

        return $noticia;
    }

    /**
     * Alterna el estado publicado de una noticia.
     */
    public function togglePublicado(int $id): CmsNoticias
    {
        $noticia = $this->obtenerPorId($id);
        $noticia->update(['not_publicado' => ! $noticia->not_publicado]);

        return $noticia;
    }

    /**
     * Elimina una noticia y su archivo de portada asociado.
     */
    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->clearMediaCollection('not_portada');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
