<?php

namespace App\Imports;

use App\Models\Autor;
use App\Models\Especialidades;
use App\Models\GradoAcademico;
use App\Models\Persona;
use App\Models\TipoAutor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class AutoresImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $importedCount = 0;

    protected int $skippedCount = 0;

    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $nombres = trim($row['nombres'] ?? $row['p_nombre'] ?? $row['nombre'] ?? '');
            $apellidoPaterno = trim($row['apellido_paterno'] ?? $row['p_apellido_paterno'] ?? '');

            if (empty($nombres) || empty($apellidoPaterno)) {
                $this->errors[] = "Fila {$rowNumber}: Se omitió porque requiere al menos Nombres y Apellido Paterno.";
                $this->skippedCount++;

                continue;
            }

            try {
                DB::transaction(function () use ($row, $nombres, $apellidoPaterno) {
                    $apellidoMaterno = trim($row['apellido_materno'] ?? $row['p_apellido_materno'] ?? '') ?: null;
                    $nombreCompleto = trim("{$nombres} {$apellidoPaterno} ".($apellidoMaterno ?? ''));

                    // 1. Correo electrónico
                    $correo = trim($row['correo'] ?? $row['email'] ?? $row['p_correo'] ?? '');
                    if (empty($correo) || ! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                        $correoBase = Str::slug("{$nombres}.{$apellidoPaterno}").'@ieta.umsa.bo';
                        $correo = $correoBase;
                        $counter = 1;
                        while (Persona::where('p_correo', $correo)->exists()) {
                            $correo = Str::slug("{$nombres}.{$apellidoPaterno}")."{$counter}@ieta.umsa.bo";
                            $counter++;
                        }
                    }

                    // 2. Resolver Usuario existente (solo si ya existía previamente registrado con ese correo)
                    $user = User::where('email', $correo)->first();
                    $userId = $user?->id;

                    // 3. Resolver o Crear Persona (sin forzar la creación de usuario; se administra luego individualmente)
                    $persona = Persona::where('p_correo', $correo)->first();
                    if (! $persona) {
                        $persona = Persona::create([
                            'p_nombre' => $nombres,
                            'p_apellido_paterno' => $apellidoPaterno,
                            'p_apellido_materno' => $apellidoMaterno,
                            'p_celular' => trim($row['celular'] ?? $row['p_celular'] ?? '') ?: null,
                            'p_correo' => $correo,
                            'p_sitio_web' => trim($row['sitio_web'] ?? $row['p_sitio_web'] ?? '') ?: null,
                            'user_id' => $userId,
                        ]);
                    } else {
                        // Actualizar datos de contacto si vinieron en el Excel
                        $updatePersona = [
                            'p_celular' => trim($row['celular'] ?? $row['p_celular'] ?? '') ?: $persona->p_celular,
                            'p_sitio_web' => trim($row['sitio_web'] ?? $row['p_sitio_web'] ?? '') ?: $persona->p_sitio_web,
                        ];

                        // Si la persona no tenía usuario pero ya existe uno con su correo en el sistema, vincularlo
                        if (! $persona->user_id && $userId) {
                            $updatePersona['user_id'] = $userId;
                        }

                        $persona->update($updatePersona);
                    }

                    // 4. Resolver Grado Académico
                    $gradoId = null;
                    $gradoNombre = trim($row['grado_academico'] ?? $row['grado'] ?? '');
                    if (! empty($gradoNombre)) {
                        $grado = GradoAcademico::whereRaw('LOWER(ga_nombre) = ?', [mb_strtolower($gradoNombre)])
                            ->orWhereRaw('LOWER(ga_abreviatura) = ?', [mb_strtolower($gradoNombre)])
                            ->first();
                        if (! $grado) {
                            $grado = GradoAcademico::create([
                                'ga_nombre' => $gradoNombre,
                                'ga_abreviatura' => mb_substr($gradoNombre, 0, 4).'.',
                            ]);
                        }
                        $gradoId = $grado->id;
                    }
                    $gradoId ??= GradoAcademico::value('id') ?? GradoAcademico::create(['ga_nombre' => 'Licenciatura', 'ga_abreviatura' => 'Lic.'])->id;

                    // 5. Resolver Tipo de Autor
                    $tipoAutorId = null;
                    $tipoNombre = trim($row['tipo_autor'] ?? $row['tipo'] ?? '');
                    if (! empty($tipoNombre)) {
                        $tipo = TipoAutor::whereRaw('LOWER(ta_nombre) = ?', [mb_strtolower($tipoNombre)])->first();
                        if (! $tipo) {
                            $tipo = TipoAutor::create([
                                'ta_nombre' => $tipoNombre,
                                'ta_descripcion' => 'Creado automáticamente durante importación masiva',
                                'ta_estado' => true,
                            ]);
                        }
                        $tipoAutorId = $tipo->id;
                    }
                    $tipoAutorId ??= TipoAutor::value('id') ?? TipoAutor::create(['ta_nombre' => 'Docente Investigador', 'ta_estado' => true])->id;

                    // 6. Resolver Especialidades (admite múltiples separadas por coma o punto y coma)
                    $especialidadIds = [];
                    $rawEsp = trim($row['especialidad'] ?? '');
                    if (! empty($rawEsp)) {
                        $espNombres = preg_split('/[,;]+/', $rawEsp);
                        foreach ($espNombres as $nombreItem) {
                            $nombreItem = trim($nombreItem);
                            if (empty($nombreItem)) {
                                continue;
                            }
                            $especialidad = Especialidades::whereRaw('LOWER(ep_nombre) = ?', [mb_strtolower($nombreItem)])->first();
                            if (! $especialidad) {
                                $especialidad = Especialidades::create([
                                    'ep_nombre' => $nombreItem,
                                    'ep_descripcion' => 'Creada automáticamente durante importación masiva',
                                    'ep_estado' => true,
                                ]);
                            }
                            $especialidadIds[] = $especialidad->id;
                        }
                    }
                    if (empty($especialidadIds)) {
                        $defaultEspId = Especialidades::value('id') ?? Especialidades::create(['ep_nombre' => 'Estadística General', 'ep_estado' => true])->id;
                        $especialidadIds = [$defaultEspId];
                    }

                    // 7. Generar Slug Único para el Autor
                    $baseSlug = Str::slug("{$nombres} {$apellidoPaterno}");
                    $slug = $baseSlug ?: 'autor-'.uniqid();
                    $counter = 1;
                    while (Autor::where('at_slug', $slug)->where('persona_id', '!=', $persona->id)->exists()) {
                        $slug = "{$baseSlug}-{$counter}";
                        $counter++;
                    }

                    // 8. Crear o Actualizar Autor
                    $autor = Autor::where('persona_id', $persona->id)->first();
                    $intereses = trim($row['intereses_cientificos'] ?? $row['intereses'] ?? '') ?: null;
                    $rawBio = trim($row['biografia'] ?? $row['at_biografia'] ?? $row['bio'] ?? $row['perfil'] ?? '');
                    $biografiaBlocks = $this->textoABloquesEditorJs($rawBio);

                    if ($autor) {
                        $updateData = [
                            'tipo_autor_id' => $tipoAutorId ?? $autor->tipo_autor_id,
                            'grado_academico_id' => $gradoId ?? $autor->grado_academico_id,
                            'at_intereses_cientificos' => $intereses ?? $autor->at_intereses_cientificos,
                            'at_estado' => true,
                        ];

                        if (! empty($biografiaBlocks['blocks'])) {
                            $updateData['at_biografia'] = $biografiaBlocks;
                        }

                        $autor->update($updateData);
                        $autor->especialidades()->sync($especialidadIds);
                    } else {
                        $autor = Autor::create([
                            'persona_id' => $persona->id,
                            'tipo_autor_id' => $tipoAutorId,
                            'grado_academico_id' => $gradoId,
                            'at_biografia' => $biografiaBlocks,
                            'at_slug' => $slug,
                            'at_intereses_cientificos' => $intereses,
                            'at_estado' => true,
                        ]);
                        $autor->especialidades()->sync($especialidadIds);
                    }

                    $this->importedCount++;
                });
            } catch (Throwable $e) {
                $this->errors[] = "Fila {$rowNumber}: Error al procesar '{$nombres} {$apellidoPaterno}' - ".$e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    /**
     * Convierte texto plano o multilínea en la estructura JSON de bloques de Editor.js.
     */
    protected function textoABloquesEditorJs(?string $texto): array
    {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return ['blocks' => []];
        }

        if (str_starts_with($texto, '{') && str_ends_with($texto, '}')) {
            $decoded = json_decode($texto, true);
            if (is_array($decoded) && isset($decoded['blocks']) && is_array($decoded['blocks'])) {
                return $decoded;
            }
        }

        $parrafos = preg_split('/\r\n\r\n|\n\n|\r\r/', $texto);
        $blocks = [];

        foreach ($parrafos as $parrafo) {
            $parrafoLimpio = trim($parrafo);
            if ($parrafoLimpio !== '') {
                $htmlTexto = nl2br(htmlspecialchars($parrafoLimpio, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                $blocks[] = [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => $htmlTexto,
                    ],
                ];
            }
        }

        if (empty($blocks)) {
            return ['blocks' => []];
        }

        return [
            'time' => (int) (microtime(true) * 1000),
            'blocks' => $blocks,
            'version' => '2.29.0',
        ];
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
