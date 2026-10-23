<?php

namespace App\Services;

use App\Models\CmsGaleria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsGaleriaService
{
    /**
     * Categorías predefinidas recomendadas para la galería del IETA.
     */
    public const CATEGORIAS_DEFAULT = [
        'Institucional',
        'Eventos y Actividades',
        'Investigación y Estadística',
        'Infraestructura y Ambientes',
        'Académico y Cursos',
        'Convenios y Autoridades',
        'Revista Varianza',
    ];

    /**
     * Obtiene registros paginados con filtros de búsqueda, categoría, destacado y activo.
     */
    public function obtenerTodos(
        ?string $search = null,
        ?string $categoria = null,
        ?bool $destacado = null,
        ?bool $activo = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = CmsGaleria::with('media')
            ->orderBy('gal_orden', 'asc')
            ->latest('id');

        if ($categoria !== null && trim($categoria) !== '') {
            $query->where('gal_categoria', $categoria);
        }

        if ($destacado !== null) {
            $query->where('gal_destacado', $destacado);
        }

        if ($activo !== null) {
            $query->where('gal_activo', $activo);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }

                $q->orWhere('gal_titulo', 'like', "%{$term}%")
                    ->orWhere('gal_descripcion', 'like', "%{$term}%")
                    ->orWhere('gal_categoria', 'like', "%{$term}%")
                    ->orWhere('gal_lugar', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtiene una imagen por ID con su archivo multimedia.
     */
    public function obtenerPorId(int $id): CmsGaleria
    {
        return CmsGaleria::with('media')->findOrFail($id);
    }

    /**
     * Registra una nueva imagen en la galería.
     */
    public function guardar(array $data, ?UploadedFile $imagen = null): CmsGaleria
    {
        try {
            DB::beginTransaction();

            $registro = CmsGaleria::create([
                'gal_titulo' => $data['gal_titulo'],
                'gal_descripcion' => $data['gal_descripcion'] ?? null,
                'gal_categoria' => $data['gal_categoria'] ?? 'General',
                'gal_fecha' => $data['gal_fecha'] ?? now()->toDateString(),
                'gal_lugar' => $data['gal_lugar'] ?? null,
                'gal_destacado' => (bool) ($data['gal_destacado'] ?? false),
                'gal_activo' => isset($data['gal_activo']) ? (bool) $data['gal_activo'] : true,
                'gal_orden' => (int) ($data['gal_orden'] ?? 0),
            ]);

            if ($imagen) {
                $registro->addMedia($imagen)->toMediaCollection('gal_imagen');
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una imagen de la galería existente.
     */
    public function actualizar(int $id, array $data, ?UploadedFile $imagen = null): CmsGaleria
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'gal_titulo' => $data['gal_titulo'] ?? $registro->gal_titulo,
                'gal_descripcion' => array_key_exists('gal_descripcion', $data) ? $data['gal_descripcion'] : $registro->gal_descripcion,
                'gal_categoria' => $data['gal_categoria'] ?? $registro->gal_categoria,
                'gal_fecha' => array_key_exists('gal_fecha', $data) ? $data['gal_fecha'] : $registro->gal_fecha,
                'gal_lugar' => array_key_exists('gal_lugar', $data) ? $data['gal_lugar'] : $registro->gal_lugar,
                'gal_destacado' => array_key_exists('gal_destacado', $data) ? (bool) $data['gal_destacado'] : $registro->gal_destacado,
                'gal_activo' => array_key_exists('gal_activo', $data) ? (bool) $data['gal_activo'] : $registro->gal_activo,
                'gal_orden' => array_key_exists('gal_orden', $data) ? (int) $data['gal_orden'] : $registro->gal_orden,
            ]);

            if ($imagen) {
                $registro->clearMediaCollection('gal_imagen');
                $registro->addMedia($imagen)->toMediaCollection('gal_imagen');
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un elemento de la galería y su archivo multimedia.
     */
    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->clearMediaCollection('gal_imagen');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Alterna el estado destacado (para carruseles y banners web).
     */
    public function toggleDestacado(int $id): CmsGaleria
    {
        $registro = $this->obtenerPorId($id);
        $registro->update(['gal_destacado' => ! $registro->gal_destacado]);

        return $registro;
    }

    /**
     * Alterna la visibilidad pública (activo/borrador).
     */
    public function toggleActivo(int $id): CmsGaleria
    {
        $registro = $this->obtenerPorId($id);
        $registro->update(['gal_activo' => ! $registro->gal_activo]);

        return $registro;
    }

    /**
     * Retorna todas las categorías existentes y sugeridas.
     */
    public function obtenerCategorias(): array
    {
        $dbCategorias = CmsGaleria::select('gal_categoria')
            ->distinct()
            ->whereNotNull('gal_categoria')
            ->pluck('gal_categoria')
            ->toArray();

        return array_values(array_unique(array_merge(self::CATEGORIAS_DEFAULT, $dbCategorias)));
    }

    /**
     * Retorna imágenes activas para la API pública del sitio web.
     */
    public function obtenerParaApi(?string $categoria = null, ?bool $destacado = null, int $limit = 24): Collection
    {
        $query = CmsGaleria::with('media')
            ->where('gal_activo', true)
            ->orderBy('gal_orden', 'asc')
            ->latest('id');

        if ($categoria !== null && trim($categoria) !== '') {
            $query->where('gal_categoria', $categoria);
        }

        if ($destacado !== null) {
            $query->where('gal_destacado', $destacado);
        }

        return $query->take($limit)->get();
    }

    /**
     * Métricas rápidas de la galería.
     */
    public function obtenerEstadisticas(): array
    {
        return [
            'total' => CmsGaleria::count(),
            'destacadas' => CmsGaleria::where('gal_destacado', true)->count(),
            'activas' => CmsGaleria::where('gal_activo', true)->count(),
            'categorias' => CmsGaleria::distinct('gal_categoria')->count('gal_categoria'),
        ];
    }
}
