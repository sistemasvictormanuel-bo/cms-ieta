<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\EdicionVarianza;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
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
                'ev_enlace_url' => ! empty($data['ev_enlace_url']) ? trim($data['ev_enlace_url']) : null,
                'ev_fecha_publicacion' => $data['ev_fecha_publicacion'] ?? null,
                'ev_estado' => isset($data['ev_estado']) ? (bool) $data['ev_estado'] : true,
            ]);

            if ($portada) {
                $registro->addMedia($portada)->toMediaCollection('ev_portada');
            }

            $savedPdfMedia = null;
            if ($archivo) {
                $savedPdfMedia = $registro->addMedia($archivo)->toMediaCollection('ev_archivo');
            }

            // Si no se proporcionó portada manual y se tiene un PDF o enlace externo:
            $solicitaExtraccion = ! empty($data['extraer_portada_de_pdf']);
            $tieneEnlace = ! empty($data['ev_enlace_url']);
            if (! $portada && ($solicitaExtraccion || $archivo || $tieneEnlace)) {
                $pdfPath = ($savedPdfMedia && file_exists($savedPdfMedia->getPath())) ? $savedPdfMedia->getPath() : null;
                $this->extraerPortadaDesdePdfOEnlace($registro, $pdfPath, $data['ev_enlace_url'] ?? null);
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
                'ev_enlace_url' => array_key_exists('ev_enlace_url', $data) ? (! empty($data['ev_enlace_url']) ? trim($data['ev_enlace_url']) : null) : $registro->ev_enlace_url,
                'ev_fecha_publicacion' => array_key_exists('ev_fecha_publicacion', $data) ? $data['ev_fecha_publicacion'] : $registro->ev_fecha_publicacion,
                'ev_estado' => isset($data['ev_estado']) ? (bool) $data['ev_estado'] : $registro->ev_estado,
            ]);

            if ($portada) {
                $registro->clearMediaCollection('ev_portada');
                $registro->addMedia($portada)->toMediaCollection('ev_portada');
            }

            $savedPdfMedia = null;
            if ($archivo) {
                $registro->clearMediaCollection('ev_archivo');
                $savedPdfMedia = $registro->addMedia($archivo)->toMediaCollection('ev_archivo');
            }

            $solicitaExtraccion = ! empty($data['extraer_portada_de_pdf']);
            $enlaceUrlActual = $data['ev_enlace_url'] ?? $registro->ev_enlace_url;
            $tieneNuevoEnlace = ! empty($data['ev_enlace_url']) && $data['ev_enlace_url'] !== $registro->getOriginal('ev_enlace_url');
            $faltaPortada = ! $registro->getFirstMedia('ev_portada');

            if (! $portada && ($solicitaExtraccion || ($archivo && $faltaPortada) || ($tieneNuevoEnlace && $faltaPortada))) {
                $pdfPath = null;
                if ($savedPdfMedia && file_exists($savedPdfMedia->getPath())) {
                    $pdfPath = $savedPdfMedia->getPath();
                } else {
                    $existingPdf = $registro->getFirstMedia('ev_archivo');
                    if ($existingPdf && file_exists($existingPdf->getPath())) {
                        $pdfPath = $existingPdf->getPath();
                    }
                }

                $this->extraerPortadaDesdePdfOEnlace($registro, $pdfPath, $enlaceUrlActual);
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Extrae automáticamente la portada desde un PDF local o desde un enlace externo (Drive, Dropbox, URL).
     */
    public function extraerPortadaDesdePdfOEnlace(
        EdicionVarianza $registro,
        string|UploadedFile|null $pdfSource = null,
        ?string $enlaceUrl = null
    ): bool {
        // 1. Intentar desde archivo PDF local si existe
        if ($pdfSource) {
            $pdfPath = $pdfSource instanceof UploadedFile ? $pdfSource->getRealPath() : $pdfSource;

            if (! empty($pdfPath) && file_exists($pdfPath) && is_readable($pdfPath)) {
                $tempImagePrefix = sys_get_temp_dir().'/cover_'.Str::uuid();

                $process = Process::run([
                    'pdftoppm',
                    '-png',
                    '-r', '150',
                    '-f', '1',
                    '-l', '1',
                    '-singlefile',
                    $pdfPath,
                    $tempImagePrefix,
                ]);

                $generatedPng = $tempImagePrefix.'.png';

                if ($process->successful() && file_exists($generatedPng)) {
                    $registro->clearMediaCollection('ev_portada');
                    $registro->addMedia($generatedPng)
                        ->usingFileName('portada_generada_'.time().'.png')
                        ->toMediaCollection('ev_portada');
                    @unlink($generatedPng);

                    return true;
                }
            }
        }

        // 2. Si no hubo archivo local o falló, intentar extraer desde enlace externo
        $url = ! empty($enlaceUrl) ? trim($enlaceUrl) : $registro->ev_enlace_url;

        if (! empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            // A) Caso Google Drive
            if (str_contains($url, 'drive.google.com') || str_contains($url, 'docs.google.com')) {
                if (preg_match('/(?:file\/d\/|open\?id=|id=|\/d\/)([a-zA-Z0-9_-]{20,})/', $url, $matches)) {
                    $fileId = $matches[1];
                    $thumbUrl = "https://drive.google.com/thumbnail?id={$fileId}&sz=w1200";

                    try {
                        $response = Http::withoutVerifying()
                            ->timeout(15)
                            ->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                            ])
                            ->get($thumbUrl);

                        if ($response->successful() && strlen($response->body()) > 50) {
                            $tempFile = sys_get_temp_dir().'/gdrive_cover_'.Str::uuid().'.png';
                            file_put_contents($tempFile, $response->body());

                            $registro->clearMediaCollection('ev_portada');
                            $registro->addMedia($tempFile)
                                ->usingFileName('portada_drive_'.time().'.png')
                                ->toMediaCollection('ev_portada');
                            @unlink($tempFile);

                            return true;
                        }
                    } catch (Throwable $e) {
                        report($e);
                    }
                }
            }

            // B) Caso Dropbox u otros enlaces directos a PDF
            try {
                $downloadUrl = $url;
                if (str_contains($url, 'dropbox.com')) {
                    $downloadUrl = str_replace(['?dl=0', '&dl=0'], ['?dl=1', '&dl=1'], $url);
                    if (! str_contains($downloadUrl, 'dl=1')) {
                        $downloadUrl .= (str_contains($downloadUrl, '?') ? '&' : '?').'dl=1';
                    }
                }

                $tempDownload = sys_get_temp_dir().'/remote_pdf_'.Str::uuid().'.pdf';
                $response = Http::withoutVerifying()
                    ->timeout(25)
                    ->sink($tempDownload)
                    ->get($downloadUrl);

                if ($response->successful() && file_exists($tempDownload) && filesize($tempDownload) > 500) {
                    $header = file_get_contents($tempDownload, false, null, 0, 10);
                    if (str_starts_with($header, '%PDF-')) {
                        $tempImagePrefix = sys_get_temp_dir().'/remote_cover_'.Str::uuid();
                        $process = Process::run([
                            'pdftoppm',
                            '-png',
                            '-r', '150',
                            '-f', '1',
                            '-l', '1',
                            '-singlefile',
                            $tempDownload,
                            $tempImagePrefix,
                        ]);

                        $generatedPng = $tempImagePrefix.'.png';
                        if ($process->successful() && file_exists($generatedPng)) {
                            $registro->clearMediaCollection('ev_portada');
                            $registro->addMedia($generatedPng)
                                ->usingFileName('portada_remota_'.time().'.png')
                                ->toMediaCollection('ev_portada');
                            @unlink($generatedPng);
                            @unlink($tempDownload);

                            return true;
                        }
                    } elseif (str_starts_with($header, "\xFF\xD8\xFF") || str_starts_with($header, "\x89PNG")) {
                        // El enlace devolvió directamente una imagen
                        $registro->clearMediaCollection('ev_portada');
                        $registro->addMedia($tempDownload)
                            ->usingFileName('portada_externa_'.time().'.png')
                            ->toMediaCollection('ev_portada');
                        @unlink($tempDownload);

                        return true;
                    }

                    @unlink($tempDownload);
                }
            } catch (Throwable $e) {
                report($e);
                if (isset($tempDownload) && file_exists($tempDownload)) {
                    @unlink($tempDownload);
                }
            }
        }

        return false;
    }

    /**
     * Alias de compatibilidad para extraer desde PDF.
     */
    public function extraerPortadaDesdePdf(EdicionVarianza $registro, string|UploadedFile $pdfSource): bool
    {
        return $this->extraerPortadaDesdePdfOEnlace($registro, $pdfSource, null);
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
