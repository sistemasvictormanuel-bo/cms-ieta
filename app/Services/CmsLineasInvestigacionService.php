<?php

namespace App\Services;

use App\Models\CmsLineasInvestigacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsLineasInvestigacionService
{
    public function obtenerTodas(?string $search = null, int $perPage = 10)
    {
        $query = CmsLineasInvestigacion::with('media')->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);

            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('li_slug', 'like', "%{$term}%")
                    ->orWhere('li_titulo', 'like', "%{$term}%")
                    ->orWhere('li_descripcion', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerPorId(int $id): CmsLineasInvestigacion
    {
        return CmsLineasInvestigacion::with('media')->findOrFail($id);
    }

    public function obtenerPorSlug(string $slug): CmsLineasInvestigacion
    {
        return CmsLineasInvestigacion::with('media')
            ->where('li_slug', $slug)
            ->firstOrFail();
    }

    public function guardar(array $data, ?UploadedFile $imagen = null): CmsLineasInvestigacion
    {
        try {
            DB::beginTransaction();

            $slug = ! empty($data['li_slug'])
                ? Str::slug($data['li_slug'])
                : Str::slug($data['li_titulo'] ?? Str::limit($data['li_descripcion'], 60));

            $registro = CmsLineasInvestigacion::create([
                'li_titulo' => $data['li_titulo'] ?? null,
                'li_descripcion' => $data['li_descripcion'],
                'li_contenido' => $data['li_contenido'],
                'li_slug' => $slug,
            ]);

            if ($imagen) {
                $registro->addMedia($imagen)->toMediaCollection('li_imagen');
            }

            DB::commit();

            return $registro->load('media');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data, ?UploadedFile $imagen = null): CmsLineasInvestigacion
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            // Generar o conservar el Slug
            if (! empty($data['li_titulo'])) {
                $slug = Str::slug($data['li_titulo']);
            } elseif (! empty($data['li_slug'])) {
                $slug = Str::slug($data['li_slug']);
            } else {
                $slug = $registro->li_slug;
            }

            $registro->update([
                'li_titulo' => $data['li_titulo'] ?? $registro->li_titulo,
                'li_descripcion' => $data['li_descripcion'] ?? $registro->li_descripcion,
                'li_contenido' => $data['li_contenido'] ?? $registro->li_contenido,
                'li_slug' => $slug,
            ]);

            if ($imagen) {
                $registro->clearMediaCollection('li_imagen');
                $registro->addMedia($imagen)->toMediaCollection('li_imagen');
            }

            DB::commit();

            return $registro->fresh('media');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = CmsLineasInvestigacion::findOrFail($id);

            // Elimina también los archivos adjuntos en Spatie uwuw importan
            $registro->clearMediaCollection('li_imagen');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
