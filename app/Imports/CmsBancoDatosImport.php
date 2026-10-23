<?php

namespace App\Imports;

use App\Models\AreaTematicas;
use App\Models\CmsBancoDatos;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class CmsBancoDatosImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $importedCount = 0;

    protected int $skippedCount = 0;

    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 índice base 0, +1 fila encabezado

            $titulo = trim($row['titulo'] ?? $row['bdd_titulo'] ?? $row['nombre'] ?? '');
            if (empty($titulo)) {
                $this->errors[] = "Fila {$rowNumber}: Se omitió porque requiere al menos el Título de la base de datos.";
                $this->skippedCount++;

                continue;
            }

            try {
                DB::transaction(function () use ($row, $titulo) {
                    // 1. Resolver Código de Identificación
                    $codigo = trim($row['codigo'] ?? $row['bdd_codigo'] ?? $row['cod'] ?? '');
                    if (empty($codigo)) {
                        $codigo = Str::slug(mb_substr($titulo, 0, 50));
                        if (empty($codigo)) {
                            $codigo = 'base-'.uniqid();
                        }
                    }

                    // 2. Resolver Tipo de Base de Datos ('Censos' o 'Encuestas')
                    $tipoRaw = mb_strtolower(trim((string) ($row['tipo_dato'] ?? $row['bdd_tipo_dato'] ?? $row['tipo'] ?? '')));
                    if (str_contains($tipoRaw, 'encuesta') || str_contains($tipoRaw, 'eh') || str_contains($tipoRaw, 'ece')) {
                        $tipoDato = 'Encuestas';
                    } elseif (str_contains($tipoRaw, 'censo') || str_contains($tipoRaw, 'cnpv') || str_contains($tipoRaw, 'cs')) {
                        $tipoDato = 'Censos';
                    } else {
                        // Inferencia por título o código
                        $tituloLower = mb_strtolower($titulo.' '.$codigo);
                        if (str_contains($tituloLower, 'encuesta') || str_contains($tituloLower, 'hogar')) {
                            $tipoDato = 'Encuestas';
                        } else {
                            $tipoDato = 'Censos';
                        }
                    }

                    // 3. Resolver Año
                    $anioRaw = trim((string) ($row['anio'] ?? $row['bdd_anio'] ?? $row['ano'] ?? ''));
                    $anio = null;
                    if (is_numeric($anioRaw) && (int) $anioRaw >= 1900 && (int) $anioRaw <= 2100) {
                        $anio = (int) $anioRaw;
                    } else {
                        // Buscar año de 4 dígitos en el título o código (ej: 1992, 2012, 2024)
                        if (preg_match('/\b(19\d\d|20\d\d)\b/', $titulo.' '.$codigo, $matches)) {
                            $anio = (int) $matches[1];
                        } else {
                            $anio = (int) date('Y');
                        }
                    }

                    // 4. Locación / Cobertura geográfica
                    $locacion = trim((string) ($row['locacion'] ?? $row['bdd_locacion'] ?? $row['cobertura'] ?? '')) ?: 'Bolivia';

                    // 5. Enlace Externo (URL)
                    $url = trim((string) ($row['url'] ?? $row['bdd_url'] ?? $row['enlace'] ?? '')) ?: null;

                    // 6. Descripción
                    $descripcion = trim((string) ($row['descripcion'] ?? $row['bdd_descripcion'] ?? '')) ?: null;

                    // 7. Crear o actualizar registro
                    $banco = CmsBancoDatos::updateOrCreate(
                        ['bdd_codigo' => $codigo],
                        [
                            'bdd_tipo_dato' => $tipoDato,
                            'bdd_anio' => $anio,
                            'bdd_titulo' => $titulo,
                            'bdd_descripcion' => $descripcion,
                            'bdd_locacion' => $locacion,
                            'bdd_url' => $url,
                        ]
                    );

                    // 8. Resolver Áreas Temáticas asociadas
                    $areasRaw = trim((string) ($row['areas_tematicas'] ?? $row['areas'] ?? $row['area_tematica'] ?? ''));
                    if (! empty($areasRaw)) {
                        $areaNames = array_filter(array_map('trim', preg_split('/[,;]+/', $areasRaw)));
                        $areaIds = [];

                        foreach ($areaNames as $areaName) {
                            if (empty($areaName)) {
                                continue;
                            }

                            $area = AreaTematicas::whereRaw('LOWER(at_nombre) = ?', [mb_strtolower($areaName)])->first();
                            if (! $area) {
                                $area = AreaTematicas::create([
                                    'at_nombre' => $areaName,
                                    'at_descripcion' => 'Área temática agregada automáticamente mediante importación masiva.',
                                ]);
                            }
                            $areaIds[] = $area->id;
                        }

                        if (! empty($areaIds)) {
                            $banco->areasTematicas()->sync($areaIds);
                        }
                    }

                    $this->importedCount++;
                });
            } catch (Throwable $e) {
                $this->errors[] = "Fila {$rowNumber}: Error al procesar ({$e->getMessage()})";
                $this->skippedCount++;
            }
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
