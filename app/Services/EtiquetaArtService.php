<?php

namespace App\Services;

use App\Models\EtiquetaArt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class EtiquetaArtService
{
    /**
     * Obtiene etiquetas paginadas con búsqueda por nombre o slug, y conteo de artículos.
     */
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = EtiquetaArt::withCount('articulos')->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ea_nombre', 'like', "%{$term}%")
                    ->orWhere('ea_slug', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Retorna lista completa ordenada para selectores dinámicos en formularios.
     */
    public function obtenerListaParaSelect(): Collection
    {
        return EtiquetaArt::select(['id', 'ea_nombre', 'ea_slug'])
            ->withCount('articulos')
            ->orderBy('ea_nombre')
            ->get();
    }

    public function obtenerPorId(int $id): EtiquetaArt
    {
        return EtiquetaArt::withCount('articulos')->findOrFail($id);
    }

    public function obtenerPorSlug(string $slug): EtiquetaArt
    {
        return EtiquetaArt::withCount(['articulos' => function ($q) {
            $q->where('estado_revision', 'aprobado')->where('art_estado', true);
        }])
            ->with(['articulos' => function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true)->latest('id');
            }])
            ->where('ea_slug', $slug)
            ->firstOrFail();
    }

    /**
     * Registra una nueva etiqueta asegurando slug único en vivo / autogenerado.
     */
    public function guardar(array $data): EtiquetaArt
    {
        $baseSlug = ! empty($data['ea_slug'])
            ? Str::slug($data['ea_slug'])
            : Str::slug($data['ea_nombre']);

        $slug = $baseSlug;
        $counter = 1;
        while (EtiquetaArt::where('ea_slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return EtiquetaArt::create([
            'ea_nombre' => trim($data['ea_nombre']),
            'ea_slug' => $slug,
        ]);
    }

    /**
     * Actualiza una etiqueta existente asegurando slug único.
     */
    public function actualizar(int $id, array $data): EtiquetaArt
    {
        $registro = $this->obtenerPorId($id);

        $slug = $registro->ea_slug;
        if (! empty($data['ea_slug']) && $data['ea_slug'] !== $registro->ea_slug) {
            $baseSlug = Str::slug($data['ea_slug']);
            $slug = $baseSlug;
            $counter = 1;
            while (EtiquetaArt::where('ea_slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
        } elseif (empty($slug) && ! empty($data['ea_nombre'])) {
            $baseSlug = Str::slug($data['ea_nombre']);
            $slug = $baseSlug;
            $counter = 1;
            while (EtiquetaArt::where('ea_slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
        }

        $registro->update([
            'ea_nombre' => trim($data['ea_nombre'] ?? $registro->ea_nombre),
            'ea_slug' => $slug,
        ]);

        return $registro->fresh(['articulos']);
    }

    public function eliminar(int $id): bool
    {
        $registro = $this->obtenerPorId($id);

        return (bool) $registro->delete();
    }
}
