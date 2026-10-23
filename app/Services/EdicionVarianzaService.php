<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\EdicionVarianza;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class EdicionVarianzaService
{
    /**
     * Obtiene el listado paginado de ediciones de la Revista Varianza con filtros.
     */
    public function obtenerTodos(?string $search = null, ?int $anio = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = EdicionVarianza::with(['media'])
            ->withCount([
                'articulos',
                'articulos as articulos_originales_count' => function ($q) {
                    $q->whereHas('tipoArticulo', function ($tq) {
                        $tq->where('tar_nombre', 'like', '%Original%');
                    });
                },
                'articulos as notas_cientificas_count' => function ($q) {
                    $q->whereHas('tipoArticulo', function ($tq) {
                        $tq->where('tar_nombre', 'like', '%Nota%');
                    });
                },
            ])
            ->latest('ev_anio')
            ->latest('id');

        if ($anio) {
            $query->where('ev_anio', $anio);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term)
                        ->orWhere('ev_anio', (int) $term)
                        ->orWhere('ev_numero', 'like', "%{$term}%");
                }
                $q->orWhere('ev_titulo', 'like', "%{$term}%")
                    ->orWhere('ev_numero', 'like', "%{$term}%")
                    ->orWhere('ev_volumen', 'like', "%{$term}%")
                    ->orWhere('ev_slug', 'like', "%{$term}%")
                    ->orWhere('ev_doi', 'like', "%{$term}%")
                    ->orWhere('ev_issn_ver_impresa', 'like', "%{$term}%")
                    ->orWhere('ev_issn_ver_electronica', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Lista compacta para selects de asignación en artículos.
     */
    public function obtenerListaParaSelect(): Collection
    {
        return EdicionVarianza::select(['id', 'ev_titulo', 'ev_numero', 'ev_volumen', 'ev_anio', 'ev_slug'])
            ->orderByDesc('ev_anio')
            ->latest('id')
            ->get();
    }

    public function obtenerPorId(int $id, bool $soloAprobados = false): EdicionVarianza
    {
        $relations = [
            'media',
            'articulos.tipoArticulo',
            'articulos.autores.persona',
            'articulos.etiquetas',
            'articulos.media',
        ];

        if ($soloAprobados) {
            $relations['articulos'] = function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true);
            };
        } else {
            $relations[] = 'articulos';
        }

        $withCounts = [
            'articulos as articulos_originales_count' => function ($q) use ($soloAprobados) {
                if ($soloAprobados) {
                    $q->where('estado_revision', 'aprobado')->where('art_estado', true);
                }
                $q->whereHas('tipoArticulo', function ($tq) {
                    $tq->where('tar_nombre', 'like', '%Original%');
                });
            },
            'articulos as notas_cientificas_count' => function ($q) use ($soloAprobados) {
                if ($soloAprobados) {
                    $q->where('estado_revision', 'aprobado')->where('art_estado', true);
                }
                $q->whereHas('tipoArticulo', function ($tq) {
                    $tq->where('tar_nombre', 'like', '%Nota%');
                });
            },
        ];

        if ($soloAprobados) {
            $withCounts['articulos'] = function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true);
            };
        } else {
            $withCounts[] = 'articulos';
        }

        return EdicionVarianza::with($relations)
            ->withCount($withCounts)
            ->findOrFail($id);
    }

    public function obtenerPorSlug(string $slug, bool $soloAprobados = false): EdicionVarianza
    {
        $relations = [
            'media',
            'articulos.tipoArticulo',
            'articulos.autores.persona',
            'articulos.etiquetas',
            'articulos.media',
        ];

        if ($soloAprobados) {
            $relations['articulos'] = function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true);
            };
        } else {
            $relations[] = 'articulos';
        }

        $withCounts = [
            'articulos as articulos_originales_count' => function ($q) use ($soloAprobados) {
                if ($soloAprobados) {
                    $q->where('estado_revision', 'aprobado')->where('art_estado', true);
                }
                $q->whereHas('tipoArticulo', function ($tq) {
                    $tq->where('tar_nombre', 'like', '%Original%');
                });
            },
            'articulos as notas_cientificas_count' => function ($q) use ($soloAprobados) {
                if ($soloAprobados) {
                    $q->where('estado_revision', 'aprobado')->where('art_estado', true);
                }
                $q->whereHas('tipoArticulo', function ($tq) {
                    $tq->where('tar_nombre', 'like', '%Nota%');
                });
            },
        ];

        if ($soloAprobados) {
            $withCounts['articulos'] = function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true);
            };
        } else {
            $withCounts[] = 'articulos';
        }

        return EdicionVarianza::with($relations)
            ->withCount($withCounts)
            ->where('ev_slug', $slug)
            ->firstOrFail();
    }

    /**
     * Retorna la edición más reciente publicada para portadas y home.
     */
    public function obtenerUltimaEdicion(bool $soloAprobados = false): ?EdicionVarianza
    {
        $relations = [
            'media',
            'articulos.tipoArticulo',
            'articulos.autores.persona',
            'articulos.etiquetas',
        ];

        if ($soloAprobados) {
            $relations['articulos'] = function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true);
            };
        } else {
            $relations[] = 'articulos';
        }

        return EdicionVarianza::with($relations)
            ->where('ev_estado', true)
            ->latest('ev_anio')
            ->latest('ev_fecha_publicacion')
            ->first();
    }

    public function guardar(array $data, ?UploadedFile $portada = null, ?UploadedFile $archivo = null): EdicionVarianza
    {
        try {
            DB::beginTransaction();

            $baseSlug = ! empty($data['ev_slug'])
                ? Str::slug($data['ev_slug'])
                : Str::slug($data['ev_titulo'] ?? 'edicion-varianza');

            $slug = $baseSlug;
            $counter = 1;
            while (EdicionVarianza::where('ev_slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $registro = EdicionVarianza::create([
                'ev_titulo' => $data['ev_titulo'],
                'ev_numero' => $data['ev_numero'],
                'ev_volumen' => $data['ev_volumen'] ?? null,
                'ev_anio' => (int) $data['ev_anio'],
                'ev_mes' => $data['ev_mes'] ?? null,
                'ev_slug' => $slug,
                'ev_issn_ver_impresa' => $data['ev_issn_ver_impresa'] ?? null,
                'ev_issn_ver_electronica' => $data['ev_issn_ver_electronica'] ?? null,
                'ev_num_art_originales' => ! empty($data['ev_num_art_originales']) ? (int) $data['ev_num_art_originales'] : null,
                'ev_notas_cientificas' => ! empty($data['ev_notas_cientificas']) ? (int) $data['ev_notas_cientificas'] : null,
                'ev_num_paginas' => ! empty($data['ev_num_paginas']) ? (int) $data['ev_num_paginas'] : null,
                'ev_doi' => $data['ev_doi'] ?? null,
                'ev_presentacion' => $data['ev_presentacion'] ?? null,
                'ev_editorial' => $data['ev_editorial'] ?? null,
                'ev_fecha_publicacion' => $data['ev_fecha_publicacion'] ?? null,
                'ev_estado' => isset($data['ev_estado']) ? (bool) $data['ev_estado'] : true,
            ]);

            if ($portada) {
                $registro->addMedia($portada)->toMediaCollection('ev_portada');
            }

            if ($archivo) {
                $registro->addMedia($archivo)->toMediaCollection('ev_archivo');
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data, ?UploadedFile $portada = null, ?UploadedFile $archivo = null): EdicionVarianza
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $slug = $registro->ev_slug;
            if (! empty($data['ev_slug']) && $data['ev_slug'] !== $registro->ev_slug) {
                $baseSlug = Str::slug($data['ev_slug']);
                $slug = $baseSlug;
                $counter = 1;
                while (EdicionVarianza::where('ev_slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
            } elseif (empty($slug) && ! empty($data['ev_titulo'])) {
                $baseSlug = Str::slug($data['ev_titulo']);
                $slug = $baseSlug;
                $counter = 1;
                while (EdicionVarianza::where('ev_slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
            }

            $registro->update([
                'ev_titulo' => $data['ev_titulo'] ?? $registro->ev_titulo,
                'ev_numero' => $data['ev_numero'] ?? $registro->ev_numero,
                'ev_volumen' => array_key_exists('ev_volumen', $data) ? $data['ev_volumen'] : $registro->ev_volumen,
                'ev_anio' => isset($data['ev_anio']) ? (int) $data['ev_anio'] : $registro->ev_anio,
                'ev_mes' => array_key_exists('ev_mes', $data) ? $data['ev_mes'] : $registro->ev_mes,
                'ev_slug' => $slug,
                'ev_issn_ver_impresa' => array_key_exists('ev_issn_ver_impresa', $data) ? $data['ev_issn_ver_impresa'] : $registro->ev_issn_ver_impresa,
                'ev_issn_ver_electronica' => array_key_exists('ev_issn_ver_electronica', $data) ? $data['ev_issn_ver_electronica'] : $registro->ev_issn_ver_electronica,
                'ev_num_art_originales' => array_key_exists('ev_num_art_originales', $data) ? (! empty($data['ev_num_art_originales']) ? (int) $data['ev_num_art_originales'] : null) : $registro->ev_num_art_originales,
                'ev_notas_cientificas' => array_key_exists('ev_notas_cientificas', $data) ? (! empty($data['ev_notas_cientificas']) ? (int) $data['ev_notas_cientificas'] : null) : $registro->ev_notas_cientificas,
                'ev_num_paginas' => array_key_exists('ev_num_paginas', $data) ? (! empty($data['ev_num_paginas']) ? (int) $data['ev_num_paginas'] : null) : $registro->ev_num_paginas,
                'ev_doi' => array_key_exists('ev_doi', $data) ? $data['ev_doi'] : $registro->ev_doi,
                'ev_presentacion' => array_key_exists('ev_presentacion', $data) ? $data['ev_presentacion'] : $registro->ev_presentacion,
                'ev_editorial' => array_key_exists('ev_editorial', $data) ? $data['ev_editorial'] : $registro->ev_editorial,
                'ev_fecha_publicacion' => array_key_exists('ev_fecha_publicacion', $data) ? $data['ev_fecha_publicacion'] : $registro->ev_fecha_publicacion,
                'ev_estado' => isset($data['ev_estado']) ? (bool) $data['ev_estado'] : $registro->ev_estado,
            ]);

            if ($portada) {
                $registro->clearMediaCollection('ev_portada');
                $registro->addMedia($portada)->toMediaCollection('ev_portada');
            }

            if ($archivo) {
                $registro->clearMediaCollection('ev_archivo');
                $registro->addMedia($archivo)->toMediaCollection('ev_archivo');
            }

            DB::commit();

            return $registro->fresh(['media']);
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
            // Desvincular artículos de esta edición
            Articulo::where('edicion_varianza_id', $id)->update(['edicion_varianza_id' => null]);
            $registro->articulosPivot()->detach();
            $registro->clearMediaCollection('ev_portada');
            $registro->clearMediaCollection('ev_archivo');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
