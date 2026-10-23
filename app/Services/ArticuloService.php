<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\ArticuloRevisionHistorial;
use App\Models\TipoParticipacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ArticuloService
{
    public function obtenerTodos(
        ?string $search = null,
        ?int $tipoId = null,
        ?int $edicionId = null,
        int $perPage = 10,
        ?int $autorId = null,
        ?int $userId = null,
        ?string $estadoRevision = null,
        bool $soloAprobados = false,
        ?string $autorSlug = null,
        ?string $etiquetaSlug = null
    ): LengthAwarePaginator {
        $query = Articulo::with([
            'media',
            'autores.persona',
            'autores.gradoAcademico',
            'etiquetas',
            'tipoArticulo',
            'edicion',
            'revisadoPor',
            'creadoPor',
        ])->latest('id');

        if ($soloAprobados) {
            $query->where('estado_revision', 'aprobado')->where('art_estado', true);
        } elseif ($estadoRevision) {
            $query->where('estado_revision', $estadoRevision);
        }

        if ($autorId || $userId || $autorSlug) {
            $query->where(function ($q) use ($autorId, $userId, $autorSlug) {
                if ($autorId) {
                    $q->whereHas('autores', function ($aq) use ($autorId) {
                        $aq->where('cms_autores.id', $autorId);
                    });
                }
                if ($autorSlug) {
                    $q->whereHas('autores', function ($aq) use ($autorSlug) {
                        $aq->where('cms_autores.at_slug', $autorSlug);
                    });
                }
                if ($userId) {
                    $q->orWhere('created_by_user_id', $userId);
                }
            });
        }

        if ($etiquetaSlug) {
            $query->whereHas('etiquetas', function ($eq) use ($etiquetaSlug) {
                $eq->where('ea_slug', $etiquetaSlug);
            });
        }

        if ($tipoId) {
            $query->where('tipo_articulo_id', $tipoId);
        }

        if ($edicionId) {
            $query->where('edicion_varianza_id', $edicionId);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('art_titulo', 'like', "%{$term}%")
                    ->orWhere('art_slug', 'like', "%{$term}%")
                    ->orWhere('art_doi', 'like', "%{$term}%")
                    ->orWhere('art_orcid', 'like', "%{$term}%")
                    ->orWhere('art_resumen', 'like', "%{$term}%")
                    ->orWhereHas('etiquetas', function ($eq) use ($term) {
                        $eq->where('ea_nombre', 'like', "%{$term}%")
                            ->orWhere('ea_slug', 'like', "%{$term}%");
                    })
                    ->orWhereHas('tipoArticulo', function ($tq) use ($term) {
                        $tq->where('tar_nombre', 'like', "%{$term}%");
                    })
                    ->orWhereHas('edicion', function ($eq) use ($term) {
                        $eq->where('ev_titulo', 'like', "%{$term}%")
                            ->orWhere('ev_numero', 'like', "%{$term}%");
                    })
                    ->orWhereHas('autores.persona', function ($pq) use ($term) {
                        $pq->where('p_nombre', 'like', "%{$term}%")
                            ->orWhere('p_apellido_paterno', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerPublicos(
        ?string $search = null,
        ?int $tipoId = null,
        ?int $edicionId = null,
        int $perPage = 20,
        ?int $autorId = null,
        ?string $autorSlug = null,
        ?string $etiquetaSlug = null
    ): LengthAwarePaginator {
        return $this->obtenerTodos(
            search: $search,
            tipoId: $tipoId,
            edicionId: $edicionId,
            perPage: $perPage,
            autorId: $autorId,
            userId: null,
            estadoRevision: null,
            soloAprobados: true,
            autorSlug: $autorSlug,
            etiquetaSlug: $etiquetaSlug
        );
    }

    public function obtenerPorId(int $id): Articulo
    {
        return Articulo::with([
            'media',
            'etiquetas',
            'tipoArticulo',
            'edicion.media',
            'autores.persona',
            'autores.tipoAutor',
            'autores.especialidades',
            'autores.gradoAcademico',
        ])->findOrFail($id);
    }

    public function obtenerPorSlug(string $slug): Articulo
    {
        return Articulo::with([
            'media',
            'etiquetas',
            'tipoArticulo',
            'edicion.media',
            'autores.persona',
            'autores.tipoAutor',
            'autores.especialidades',
            'autores.gradoAcademico',
        ])->where('art_slug', $slug)->firstOrFail();
    }

    public function guardar(array $data, ?UploadedFile $archivo = null): Articulo
    {
        try {
            DB::beginTransaction();

            $baseSlug = ! empty($data['art_slug'])
                ? Str::slug($data['art_slug'])
                : Str::slug($data['art_titulo'] ?? 'articulo');

            $slug = $baseSlug;
            $counter = 1;
            while (Articulo::where('art_slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $usuario = auth()->user();
            $esAutorPuro = $usuario && $usuario->hasRole('autor') && ! $usuario->hasRole('administrador');
            $estadoRevision = $data['estado_revision'] ?? 'aprobado';
            $creadoPor = $data['created_by_user_id'] ?? auth()->id();

            // Si es un autor, el estado es forzado a 'pendiente' (o 'borrador'),
            // la fecha de recepción se toma automáticamente de la fecha del registro (hoy),
            // y la fecha de aceptación queda en null hasta que sea aprobada por un administrador.
            if ($esAutorPuro) {
                $estadoRevision = in_array($data['estado_revision'] ?? '', ['borrador', 'pendiente']) ? $data['estado_revision'] : 'pendiente';
                $fechaRecepcion = now()->toDateString();
                $fechaAceptado = null;
            } else {
                $fechaRecepcion = ! empty($data['art_fecha_recepcion']) ? $data['art_fecha_recepcion'] : now()->toDateString();
                $fechaAceptado = ! empty($data['art_fecha_aceptado']) ? $data['art_fecha_aceptado'] : null;
            }

            $registro = Articulo::create([
                'tipo_articulo_id' => ! empty($data['tipo_articulo_id']) ? (int) $data['tipo_articulo_id'] : null,
                'edicion_varianza_id' => ! empty($data['edicion_varianza_id']) ? (int) $data['edicion_varianza_id'] : null,
                'art_titulo' => $data['art_titulo'],
                'art_slug' => $slug,
                'art_resumen' => $data['art_resumen'] ?? null,
                'art_abstracto' => $data['art_abstracto'] ?? null,
                'art_fecha_recepcion' => $fechaRecepcion,
                'art_fecha_aceptado' => $fechaAceptado,
                'art_orcid' => ! empty($data['art_orcid']) ? $data['art_orcid'] : null,
                'art_doi' => ! empty($data['art_doi']) ? $data['art_doi'] : null,
                'art_num_paginas' => ! empty($data['art_num_paginas']) ? (int) $data['art_num_paginas'] : null,
                'art_pag_inicio' => ! empty($data['art_pag_inicio']) ? (int) $data['art_pag_inicio'] : null,
                'art_pag_fin' => ! empty($data['art_pag_fin']) ? (int) $data['art_pag_fin'] : null,
                'art_estado' => isset($data['art_estado']) ? (bool) $data['art_estado'] : ($estadoRevision === 'aprobado'),
                'estado_revision' => $estadoRevision,
                'created_by_user_id' => $creadoPor,
                'art_referencias' => $data['art_referencias'] ?? null,
                'art_vistas' => $data['art_vistas'] ?? 0,
            ]);

            // Sincronizar etiquetas N:M
            if (isset($data['etiquetas']) && is_array($data['etiquetas'])) {
                $registro->etiquetas()->sync($data['etiquetas']);
            }

            // Sincronizar pivote de edición si se especificó
            if (! empty($data['edicion_varianza_id'])) {
                $registro->edicionesPivot()->sync([
                    $data['edicion_varianza_id'] => [
                        'aev_pag_inicio' => $data['art_pag_inicio'] ?? null,
                        'aev_pag_fin' => $data['art_pag_fin'] ?? null,
                    ],
                ]);
            }

            // Si el autor autenticado no está en el array de autores, vincularlo automáticamente
            if ($usuario && $usuario->hasRole('autor')) {
                $autorModel = $usuario->autor();
                if ($autorModel) {
                    $yaAsignado = false;
                    if (isset($data['autores']) && is_array($data['autores'])) {
                        foreach ($data['autores'] as $item) {
                            if (($item['autor_id'] ?? null) == $autorModel->id) {
                                $yaAsignado = true;
                                break;
                            }
                        }
                    } else {
                        $data['autores'] = [];
                    }
                    $defaultPartId = TipoParticipacion::first()?->id;
                    if (! $yaAsignado) {
                        $data['autores'][] = [
                            'autor_id' => $autorModel->id,
                            'tipo_participaciones_id' => $defaultPartId,
                        ];
                    }
                }
            }

            // Sincronizar autores con tipo de participación
            if (isset($data['autores']) && is_array($data['autores'])) {
                $syncData = [];
                $defaultPartId = TipoParticipacion::first()?->id;
                foreach ($data['autores'] as $item) {
                    if (! empty($item['autor_id'])) {
                        $syncData[$item['autor_id']] = [
                            'tipo_participaciones_id' => ! empty($item['tipo_participaciones_id']) ? $item['tipo_participaciones_id'] : $defaultPartId,
                        ];
                    }
                }
                $registro->autores()->sync($syncData);
            }

            // Registrar en historial si nació en pendiente
            if ($estadoRevision === 'pendiente') {
                ArticuloRevisionHistorial::create([
                    'articulo_id' => $registro->id,
                    'user_id' => $usuario?->id,
                    'accion' => 'creado_pendiente',
                    'estado_anterior' => null,
                    'nuevo_estado' => 'pendiente',
                    'motivo' => 'Artículo enviado para revisión editorial.',
                ]);
            }

            // Archivo adjunto (PDF / Documento) Spatie Media Library
            if ($archivo) {
                $registro->addMedia($archivo)->toMediaCollection('art_archivo');
            }

            DB::commit();

            return $registro->load([
                'media',
                'tipoArticulo',
                'edicion',
                'etiquetas',
                'autores.persona',
                'autores.gradoAcademico',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data, ?UploadedFile $archivo = null): Articulo
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $slug = $registro->art_slug;
            if (! empty($data['art_slug']) && $data['art_slug'] !== $registro->art_slug) {
                $baseSlug = Str::slug($data['art_slug']);
                $slug = $baseSlug;
                $counter = 1;
                while (Articulo::where('art_slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
            } elseif (empty($slug) && ! empty($data['art_titulo'])) {
                $baseSlug = Str::slug($data['art_titulo']);
                $slug = $baseSlug;
                $counter = 1;
                while (Articulo::where('art_slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
            }

            $edicionId = array_key_exists('edicion_varianza_id', $data)
                ? (! empty($data['edicion_varianza_id']) ? (int) $data['edicion_varianza_id'] : null)
                : $registro->edicion_varianza_id;

            $usuario = auth()->user();
            $esAutorPuro = $usuario && $usuario->hasRole('autor') && ! $usuario->hasRole('administrador');
            $nuevoEstado = $registro->estado_revision;

            if ($esAutorPuro) {
                // El autor no puede alterar la fecha de recepción original ni fijar fecha de aceptación
                $fechaRecepcion = $registro->art_fecha_recepcion;
                $fechaAceptado = $registro->art_fecha_aceptado;

                // Si el autor edita un artículo que ya estaba aprobado, vuelve a pendiente y la fecha de aceptación se resetea a null
                if ($registro->estado_revision === 'aprobado') {
                    $nuevoEstado = 'pendiente';
                    $fechaAceptado = null;

                    ArticuloRevisionHistorial::create([
                        'articulo_id' => $registro->id,
                        'user_id' => $usuario->id,
                        'accion' => 're-enviado',
                        'estado_anterior' => 'aprobado',
                        'nuevo_estado' => 'pendiente',
                        'motivo' => 'Edición realizada por el autor. Pasa a revisión nuevamente.',
                    ]);
                }
            } else {
                $fechaRecepcion = array_key_exists('art_fecha_recepcion', $data) ? $data['art_fecha_recepcion'] : $registro->art_fecha_recepcion;
                $fechaAceptado = array_key_exists('art_fecha_aceptado', $data) ? $data['art_fecha_aceptado'] : $registro->art_fecha_aceptado;
            }

            $registro->update([
                'tipo_articulo_id' => array_key_exists('tipo_articulo_id', $data) ? (! empty($data['tipo_articulo_id']) ? (int) $data['tipo_articulo_id'] : null) : $registro->tipo_articulo_id,
                'edicion_varianza_id' => $edicionId,
                'art_titulo' => $data['art_titulo'] ?? $registro->art_titulo,
                'art_slug' => $slug,
                'art_resumen' => array_key_exists('art_resumen', $data) ? $data['art_resumen'] : $registro->art_resumen,
                'art_abstracto' => array_key_exists('art_abstracto', $data) ? $data['art_abstracto'] : $registro->art_abstracto,
                'art_fecha_recepcion' => $fechaRecepcion,
                'art_fecha_aceptado' => $fechaAceptado,
                'art_orcid' => array_key_exists('art_orcid', $data) ? (! empty($data['art_orcid']) ? $data['art_orcid'] : null) : $registro->art_orcid,
                'art_doi' => array_key_exists('art_doi', $data) ? (! empty($data['art_doi']) ? $data['art_doi'] : null) : $registro->art_doi,
                'art_num_paginas' => array_key_exists('art_num_paginas', $data) ? (! empty($data['art_num_paginas']) ? (int) $data['art_num_paginas'] : null) : $registro->art_num_paginas,
                'art_pag_inicio' => array_key_exists('art_pag_inicio', $data) ? (! empty($data['art_pag_inicio']) ? (int) $data['art_pag_inicio'] : null) : $registro->art_pag_inicio,
                'art_pag_fin' => array_key_exists('art_pag_fin', $data) ? (! empty($data['art_pag_fin']) ? (int) $data['art_pag_fin'] : null) : $registro->art_pag_fin,
                'art_estado' => isset($data['art_estado']) ? (bool) $data['art_estado'] : ($nuevoEstado === 'aprobado'),
                'estado_revision' => $nuevoEstado,
                'art_referencias' => array_key_exists('art_referencias', $data) ? $data['art_referencias'] : $registro->art_referencias,
                'art_vistas' => array_key_exists('art_vistas', $data) ? (int) $data['art_vistas'] : $registro->art_vistas,
            ]);

            // Sincronizar etiquetas N:M
            if (isset($data['etiquetas']) && is_array($data['etiquetas'])) {
                $registro->etiquetas()->sync($data['etiquetas']);
            }

            // Sincronizar pivote de edición
            if ($edicionId) {
                $registro->edicionesPivot()->sync([
                    $edicionId => [
                        'aev_pag_inicio' => $data['art_pag_inicio'] ?? $registro->art_pag_inicio,
                        'aev_pag_fin' => $data['art_pag_fin'] ?? $registro->art_pag_fin,
                    ],
                ]);
            } else {
                $registro->edicionesPivot()->detach();
            }

            // Sincronizar autores con tipo de participación
            if (isset($data['autores']) && is_array($data['autores'])) {
                $syncData = [];
                $defaultPartId = TipoParticipacion::first()?->id;
                foreach ($data['autores'] as $item) {
                    if (! empty($item['autor_id'])) {
                        $syncData[$item['autor_id']] = [
                            'tipo_participaciones_id' => ! empty($item['tipo_participaciones_id']) ? $item['tipo_participaciones_id'] : $defaultPartId,
                        ];
                    }
                }
                $registro->autores()->sync($syncData);
            }

            // Archivo adjunto (PDF / Documento) Spatie Media Library
            if ($archivo) {
                $registro->clearMediaCollection('art_archivo');
                $registro->addMedia($archivo)->toMediaCollection('art_archivo');
            }

            DB::commit();

            return $registro->fresh([
                'media',
                'tipoArticulo',
                'edicion',
                'etiquetas',
                'autores.persona',
                'autores.gradoAcademico',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene artículos similares basados en coincidencia de etiquetas N:M (etiquetas_art)
     * y como criterio de ordenamiento secundario el nivel de vistas.
     */
    public function obtenerSimilares(Articulo|int $articulo, int $limit = 5): Collection
    {
        $articulo = $articulo instanceof Articulo ? $articulo : $this->obtenerPorId((int) $articulo);

        $tagIds = $articulo->etiquetas()->pluck('etiquetas_art.id')->toArray();
        $query = Articulo::with([
            'media',
            'tipoArticulo',
            'edicion',
            'etiquetas',
            'autores.persona',
            'autores.gradoAcademico',
        ])
            ->where('id', '!=', $articulo->id)
            ->where('estado_revision', 'aprobado')
            ->where('art_estado', true);

        if (! empty($tagIds)) {
            $similares = (clone $query)->whereHas('etiquetas', function ($q) use ($tagIds) {
                $q->whereIn('etiquetas_art.id', $tagIds);
            })
                ->withCount(['etiquetas' => function ($q) use ($tagIds) {
                    $q->whereIn('etiquetas_art.id', $tagIds);
                }])
                ->orderByDesc('etiquetas_count')
                ->orderByRaw('COALESCE(art_vistas, 0) DESC')
                ->latest('id')
                ->limit($limit)
                ->get();

            if ($similares->count() >= $limit) {
                return $similares;
            }

            $yaIncluidos = $similares->pluck('id')->push($articulo->id)->all();
            $faltantes = $limit - $similares->count();

            $complementarios = Articulo::with([
                'media',
                'tipoArticulo',
                'edicion',
                'etiquetas',
                'autores.persona',
                'autores.gradoAcademico',
            ])
                ->whereNotIn('id', $yaIncluidos)
                ->where('estado_revision', 'aprobado')
                ->where('art_estado', true)
                ->orderByRaw('COALESCE(art_vistas, 0) DESC')
                ->latest('id')
                ->limit($faltantes)
                ->get();

            return $similares->concat($complementarios);
        }

        return $query->orderByRaw('COALESCE(art_vistas, 0) DESC')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function obtenerMasLeidosMismoAutor(Articulo|int $articulo, int $limit = 5): Collection
    {
        $articulo = $articulo instanceof Articulo ? $articulo : $this->obtenerPorId((int) $articulo);

        $autorIds = $articulo->autores()->pluck('cms_autores.id')->toArray();

        if (empty($autorIds)) {
            return new Collection;
        }

        return Articulo::with([
            'media',
            'tipoArticulo',
            'edicion',
            'etiquetas',
            'autores.persona',
            'autores.gradoAcademico',
        ])
            ->where('id', '!=', $articulo->id)
            ->where('estado_revision', 'aprobado')
            ->where('art_estado', true)
            ->whereHas('autores', function ($q) use ($autorIds) {
                $q->whereIn('cms_autores.id', $autorIds);
            })
            ->orderByRaw('COALESCE(art_vistas, 0) DESC')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->etiquetas()->detach();
            $registro->edicionesPivot()->detach();
            $registro->clearMediaCollection('art_archivo');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function incrementarVistas(int $id): void
    {
        Articulo::where('id', $id)->increment('art_vistas');
    }

    public function aprobar(int $id, int $userId, ?string $motivo = null): Articulo
    {
        try {
            DB::beginTransaction();

            $articulo = $this->obtenerPorId($id);
            $estadoAnterior = $articulo->estado_revision;

            $articulo->update([
                'estado_revision' => 'aprobado',
                'art_estado' => true,
                'art_fecha_aceptado' => $articulo->art_fecha_aceptado ?: now()->toDateString(),
                'revisado_por_user_id' => $userId,
                'revisado_at' => now(),
                'revision_motivo' => $motivo,
            ]);

            ArticuloRevisionHistorial::create([
                'articulo_id' => $articulo->id,
                'user_id' => $userId,
                'accion' => 'aprobado',
                'estado_anterior' => $estadoAnterior,
                'nuevo_estado' => 'aprobado',
                'motivo' => $motivo,
            ]);

            DB::commit();

            return $articulo;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rechazar(int $id, int $userId, string $motivo): Articulo
    {
        try {
            DB::beginTransaction();

            $articulo = $this->obtenerPorId($id);
            $estadoAnterior = $articulo->estado_revision;

            $articulo->update([
                'estado_revision' => 'rechazado',
                'art_estado' => false,
                'revisado_por_user_id' => $userId,
                'revisado_at' => now(),
                'revision_motivo' => $motivo,
            ]);

            ArticuloRevisionHistorial::create([
                'articulo_id' => $articulo->id,
                'user_id' => $userId,
                'accion' => 'rechazado',
                'estado_anterior' => $estadoAnterior,
                'nuevo_estado' => 'rechazado',
                'motivo' => $motivo,
            ]);

            DB::commit();

            return $articulo;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
