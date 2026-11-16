<?php

namespace App\Imports;

use App\Models\Articulo;
use App\Models\Autor;
use App\Models\EdicionVarianza;
use App\Models\Especialidades;
use App\Models\EtiquetaArt;
use App\Models\GradoAcademico;
use App\Models\Persona;
use App\Models\TipoArticulo;
use App\Models\TipoAutor;
use App\Models\TipoParticipacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ArticulosImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $importedCount = 0;

    protected int $skippedCount = 0;

    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 por 1-indexado, +1 por cabecera
            $titulo = trim($row['titulo'] ?? $row['art_titulo'] ?? '');

            if (empty($titulo)) {
                $this->errors[] = "Fila {$rowNumber}: Se omitió porque no contiene un título.";
                $this->skippedCount++;

                continue;
            }

            try {
                DB::transaction(function () use ($row, $titulo) {
                    // 1. Tipo de Artículo
                    $tipoId = null;
                    $tipoNombre = trim($row['tipo_articulo'] ?? $row['tipo'] ?? '');
                    if (! empty($tipoNombre)) {
                        $tipo = TipoArticulo::whereRaw('LOWER(tar_nombre) = ?', [mb_strtolower($tipoNombre)])->first();
                        if (! $tipo) {
                            $tipo = TipoArticulo::create([
                                'tar_nombre' => $tipoNombre,
                                'tar_descripcion' => 'Creado automáticamente durante importación masiva',
                                'tar_activo' => true,
                            ]);
                        }
                        $tipoId = $tipo->id;
                    }

                    // 2. Edición de Revista Varianza
                    $edicionId = null;
                    $edicionVal = trim((string) ($row['edicion'] ?? $row['edicion_varianza'] ?? ''));
                    if (! empty($edicionVal)) {
                        $edicion = null;
                        if (is_numeric($edicionVal)) {
                            $edicion = EdicionVarianza::where('ev_numero', (string) (int) $edicionVal)->first();
                        } else {
                            if (preg_match('/(?:n[°º\.]?|edici[oó]n|volumen|vol[\.]?)\s*(\d+)/i', $edicionVal, $matches)) {
                                $edicion = EdicionVarianza::where('ev_numero', (string) (int) $matches[1])->first();
                            }
                            if (! $edicion) {
                                $edicion = EdicionVarianza::whereRaw('LOWER(ev_titulo) = ?', [mb_strtolower($edicionVal)])
                                    ->orWhereRaw('LOWER(ev_titulo) LIKE ?', ['%'.mb_strtolower($edicionVal).'%'])
                                    ->first();
                            }
                        }

                        if ($edicion) {
                            $edicionId = $edicion->id;
                        }
                    }

                    // 3. Paginación
                    $pagInicio = ! empty($row['pag_inicio']) && is_numeric($row['pag_inicio']) ? (int) $row['pag_inicio'] : null;
                    $pagFin = ! empty($row['pag_fin']) && is_numeric($row['pag_fin']) ? (int) $row['pag_fin'] : null;
                    $numPaginas = ($pagInicio && $pagFin && $pagFin >= $pagInicio) ? ($pagFin - $pagInicio + 1) : null;

                    // 4. Fechas
                    $fechaRecepcion = $this->parseExcelDate($row['fecha_recepcion'] ?? null) ?? Carbon::now();
                    $fechaAceptado = $this->parseExcelDate($row['fecha_aceptado'] ?? null);

                    // 5. Enlace Externo (URL opcional a Google Drive, Dropbox, Repositorio, etc.)
                    $enlaceRaw = $row['enlace_url']
                        ?? $row['enlace_externo']
                        ?? $row['enlace']
                        ?? $row['url']
                        ?? $row['link']
                        ?? $row['drive']
                        ?? $row['art_enlace_url']
                        ?? $row['enlace_externo_url']
                        ?? '';

                    $enlaceUrl = trim((string) $enlaceRaw);
                    if (! empty($enlaceUrl)) {
                        if (! preg_match('/^https?:\/\//i', $enlaceUrl)) {
                            if (str_starts_with($enlaceUrl, 'www.') || str_starts_with($enlaceUrl, 'drive.google.com') || str_contains($enlaceUrl, '.')) {
                                $enlaceUrl = 'https://'.$enlaceUrl;
                            }
                        }
                        if (! filter_var($enlaceUrl, FILTER_VALIDATE_URL)) {
                            $enlaceUrl = null;
                        }
                    } else {
                        $enlaceUrl = null;
                    }

                    // 6. Generar Slug Único o Actualizar Artículo Existente
                    // Buscamos primero por título exacto (los títulos de papers son únicos)
                    $articuloExistente = Articulo::whereRaw('LOWER(TRIM(art_titulo)) = ?', [mb_strtolower($titulo)])->first();

                    if ($articuloExistente) {
                        $updateData = [];
                        if (! empty($enlaceUrl)) {
                            $updateData['art_enlace_url'] = $enlaceUrl;
                        }
                        if ($tipoId) {
                            $updateData['tipo_articulo_id'] = $tipoId;
                        }
                        if ($edicionId) {
                            $updateData['edicion_varianza_id'] = $edicionId;
                        }
                        if ($pagInicio) {
                            $updateData['art_pag_inicio'] = $pagInicio;
                        }
                        if ($pagFin) {
                            $updateData['art_pag_fin'] = $pagFin;
                        }
                        if (! empty($row['doi'])) {
                            $updateData['art_doi'] = $row['doi'];
                        }
                        if (! empty($row['orcid'])) {
                            $updateData['art_orcid'] = $row['orcid'];
                        }
                        if (! empty($row['resumen']) && $row['resumen'] !== 'test') {
                            $updateData['art_resumen'] = $row['resumen'];
                        }
                        $abstractVal = $row['abstract'] ?? $row['art_abstracto'] ?? null;
                        if (! empty($abstractVal) && $abstractVal !== 'test') {
                            $updateData['art_abstracto'] = $abstractVal;
                        }
                        if (! empty($row['referencias'])) {
                            $updateData['art_referencias'] = $row['referencias'];
                        }
                        if ($fechaRecepcion) {
                            $updateData['art_fecha_recepcion'] = $fechaRecepcion;
                        }
                        if ($fechaAceptado) {
                            $updateData['art_fecha_aceptado'] = $fechaAceptado;
                        }
                        if (! empty($updateData)) {
                            $articuloExistente->update($updateData);
                        }
                        $articulo = $articuloExistente;
                    } else {
                        $baseSlug = Str::slug($titulo);
                        $slug = $baseSlug ?: 'articulo-'.uniqid();
                        $counter = 1;
                        while (Articulo::where('art_slug', $slug)->exists()) {
                            $slug = "{$baseSlug}-{$counter}";
                            $counter++;
                        }

                        $articulo = Articulo::create([
                            'tipo_articulo_id' => $tipoId,
                            'edicion_varianza_id' => $edicionId,
                            'art_titulo' => $titulo,
                            'art_slug' => $slug,
                            'art_resumen' => $row['resumen'] ?? null,
                            'art_abstracto' => $row['abstract'] ?? $row['art_abstracto'] ?? null,
                            'art_fecha_recepcion' => $fechaRecepcion,
                            'art_fecha_aceptado' => $fechaAceptado,
                            'art_orcid' => $row['orcid'] ?? null,
                            'art_doi' => $row['doi'] ?? null,
                            'art_pag_inicio' => $pagInicio,
                            'art_pag_fin' => $pagFin,
                            'art_num_paginas' => $numPaginas,
                            'art_estado' => true,
                            'art_referencias' => $row['referencias'] ?? null,
                            'art_enlace_url' => $enlaceUrl,
                            'art_vistas' => 0,
                        ]);
                    }

                    // 7. Sincronizar Edición Pivot si aplica
                    if ($edicionId) {
                        $articulo->edicionesPivot()->sync([
                            $edicionId => [
                                'aev_pag_inicio' => $pagInicio,
                                'aev_pag_fin' => $pagFin,
                            ],
                        ]);
                    }

                    // 8. Procesar y vincular Etiquetas
                    $etiquetasStr = trim($row['etiquetas'] ?? $row['tags'] ?? '');
                    if (! empty($etiquetasStr)) {
                        $tags = array_filter(array_map('trim', explode(',', $etiquetasStr)));
                        $tagIds = [];
                        foreach ($tags as $tagNombre) {
                            if (empty($tagNombre)) {
                                continue;
                            }
                            $tagSlug = Str::slug($tagNombre);
                            $etiqueta = EtiquetaArt::where('ea_slug', $tagSlug)
                                ->orWhereRaw('LOWER(TRIM(ea_nombre)) = ?', [mb_strtolower($tagNombre)])
                                ->first();

                            if (! $etiqueta) {
                                $etiqueta = EtiquetaArt::create([
                                    'ea_nombre' => $tagNombre,
                                    'ea_slug' => $tagSlug ?: 'tag-'.uniqid(),
                                ]);
                            }
                            $tagIds[] = $etiqueta->id;
                        }
                        if (! empty($tagIds)) {
                            // Si ya tiene etiquetas previas (ej. cargadas a mano), conservamos las existentes
                            if (! $articulo->etiquetas()->exists()) {
                                $articulo->etiquetas()->sync($tagIds);
                            }
                        }
                    }

                    // 9. Procesar y vincular Autores
                    $autoresStr = trim($row['autores'] ?? $row['autor'] ?? '');
                    if (! empty($autoresStr)) {
                        $separador = str_contains($autoresStr, ';') ? ';' : ',';
                        $nombresAutores = array_filter(array_map('trim', explode($separador, $autoresStr)));
                        $autorIds = [];

                        foreach ($nombresAutores as $nombreAutor) {
                            if (empty($nombreAutor)) {
                                continue;
                            }
                            $autor = $this->resolverOCrearAutor($nombreAutor);
                            if ($autor) {
                                $autorIds[] = $autor->id;
                            }
                        }

                        if (! empty($autorIds)) {
                            // Si el artículo ya tiene autores asignados a mano, conservamos sus roles y asignaciones
                            if (! $articulo->autores()->exists()) {
                                $defaultParticipacionId = TipoParticipacion::value('id') ?? TipoParticipacion::create(['tp_nombre' => 'Autor'])->id;
                                $syncData = [];
                                foreach ($autorIds as $autorId) {
                                    $syncData[$autorId] = ['tipo_participaciones_id' => $defaultParticipacionId];
                                }
                                $articulo->autores()->sync($syncData);
                            }
                        }
                    }

                    $this->importedCount++;
                });
            } catch (Throwable $e) {
                $this->errors[] = "Fila {$rowNumber}: Error al procesar '{$titulo}' - ".$e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    /**
     * Resuelve un autor existente por nombre o crea un registro básico.
     */
    protected function resolverOCrearAutor(string $cadenaAutor): ?Autor
    {
        $partes = preg_split('/\s+/', trim($cadenaAutor));
        $primerNombre = $partes[0] ?? 'Investigador';
        $apellidoPaterno = $partes[1] ?? 'IETA';

        // Buscar persona que coincida con el nombre o apellido
        $persona = Persona::whereRaw("LOWER(CONCAT(p_nombre, ' ', p_apellido_paterno)) LIKE ?", ['%'.mb_strtolower($cadenaAutor).'%'])
            ->first();

        if ($persona && $persona->autor) {
            return $persona->autor;
        }

        if (! $persona) {
            $email = Str::slug($cadenaAutor).'@ieta.umsa.bo';
            $counter = 1;
            while (Persona::where('p_correo', $email)->exists()) {
                $email = Str::slug($cadenaAutor)."{$counter}@ieta.umsa.bo";
                $counter++;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $cadenaAutor, 'password' => bcrypt('Password123!')]
            );

            $persona = Persona::create([
                'p_nombre' => $primerNombre,
                'p_apellido_paterno' => $apellidoPaterno,
                'p_correo' => $email,
                'user_id' => $user->id,
            ]);
        }

        $baseSlug = Str::slug($cadenaAutor);
        $slug = $baseSlug ?: 'autor-'.uniqid();
        $counter = 1;
        while (Autor::where('at_slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $tipoAutorId = TipoAutor::value('id') ?? TipoAutor::create(['ta_nombre' => 'Docente Investigador', 'ta_estado' => true])->id;
        $especialidadId = Especialidades::value('id') ?? Especialidades::create(['ep_nombre' => 'Estadística', 'ep_estado' => true])->id;
        $gradoId = GradoAcademico::value('id') ?? GradoAcademico::create(['ga_nombre' => 'Licenciatura', 'ga_abreviatura' => 'Lic.'])->id;

        $autor = Autor::firstOrCreate(
            ['persona_id' => $persona->id],
            [
                'tipo_autor_id' => $tipoAutorId,
                'grado_academico_id' => $gradoId,
                'at_slug' => $slug,
                'at_estado' => true,
            ]
        );

        $autor->especialidades()->syncWithoutDetaching([$especialidadId]);

        return $autor;
    }

    protected function parseExcelDate(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
