<?php

namespace App\Imports;

use App\Models\Autor;
use App\Models\GradoAcademico;
use App\Models\MemoriasInvestigacion;
use App\Models\Persona;
use App\Models\TipoAutor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Throwable;

class MemoriasInvestigacionImport implements SkipsEmptyRows, ToCollection, WithEvents
{
    protected int $importedCount = 0;

    protected int $skippedCount = 0;

    protected array $errors = [];

    protected string $currentSheetName = '';

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->currentSheetName = trim($event->sheet->getDelegate()->getTitle());
            },
        ];
    }

    public function collection(Collection $rows): void
    {
        $sheetGestion = null;
        if (preg_match('/\b(19\d\d|20\d\d)\b/', $this->currentSheetName, $matches)) {
            $sheetGestion = (int) $matches[1];
        }

        $resolucionContext = null;
        $headerMap = null;
        $seccionActual = null;

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $cells = $row->map(fn ($c) => trim((string) $c))->values()->all();

            // Filtrar filas completamente vacías
            $nonEmptyCells = array_filter($cells, fn ($v) => $v !== '');
            if (empty($nonEmptyCells)) {
                continue;
            }

            $rowText = implode(' ', $nonEmptyCells);
            $rowTextAscii = Str::ascii($rowText);

            // 1. Detectar resolución institucional en encabezados (ej: "Resolución del HCF Nº 780/2015")
            if (preg_match('/resolucion.*?hcf.*?n[oº°]?\s*[\d\/\-]+/iu', $rowTextAscii, $resMatches)) {
                $resolucionContext = trim($resMatches[0]);
            }

            // 2. Detectar gestión si la hoja no tenía el año explícito en el nombre
            if (! $sheetGestion && preg_match('/gestion\s+(19\d\d|20\d\d)/iu', $rowTextAscii, $gesMatches)) {
                $sheetGestion = (int) $gesMatches[1];
            }

            // Si aún no hay gestión pero hay una resolución con año (ej: 780/2015)
            if (! $sheetGestion && preg_match('/(?:hcf[^\d]+|\/)\s*(19\d\d|20\d\d)\b/iu', $rowTextAscii, $resYearMatches)) {
                $sheetGestion = (int) $resYearMatches[1];
            }

            // 3. Detectar sección institucional (ej: "PROYECTOS DE LA CARRERA", "PROYECTOS DEL IETA")
            if (preg_match('/proyectos?\s+de\s+la\s+carrera/iu', $rowTextAscii)) {
                $seccionActual = 'Carrera de Estadística';

                continue;
            }
            if (preg_match('/proyectos?\s+del?\s+ieta/iu', $rowTextAscii)) {
                $seccionActual = 'IETA';

                continue;
            }

            // 4. Detectar si esta fila es un encabezado de columnas
            $detectedMap = $this->detectHeaderColumns($cells);
            if ($detectedMap !== null) {
                $headerMap = $detectedMap;

                continue;
            }

            // 5. Procesar fila de datos si ya se detectó el encabezado
            if ($headerMap !== null) {
                $this->processDataRow($cells, $headerMap, $sheetGestion, $resolucionContext, $seccionActual, $rowNumber);
            }
        }
    }

    /**
     * Detecta el mapeo de columnas tanto en la plantilla estándar como en el formato institucional.
     */
    protected function detectHeaderColumns(array $cells): ?array
    {
        // Si alguna celda contiene una URL o textos descriptivos muy largos, no es una fila de encabezados
        foreach ($cells as $c) {
            $trimmed = trim($c);
            if (empty($trimmed)) {
                continue;
            }
            if (filter_var($trimmed, FILTER_VALIDATE_URL) || mb_strlen($trimmed) > 60) {
                return null;
            }
        }

        $normalized = array_map(function ($c) {
            return Str::ascii(mb_strtolower(trim($c)));
        }, $cells);

        $hasDocente = false;
        $hasInvestigacion = false;
        $hasInteraccion = false;
        $hasTitulo = false;

        $map = [
            'tipo' => 'standard',
            'col_titulo' => null,
            'col_autor' => null,
            'col_gestion' => null,
            'col_etiqueta' => null,
            'col_descripcion' => null,
            'col_enlace_url' => null,
            'col_proyecto_investigacion' => null,
            'col_proyecto_interaccion' => null,
            'col_observaciones' => null,
        ];

        foreach ($normalized as $idx => $header) {
            if (empty($header)) {
                continue;
            }

            if (str_contains($header, 'docente') || str_contains($header, 'investigador') || $header === 'autor') {
                $map['col_autor'] = $idx;
                $hasDocente = true;
            }

            if (str_contains($header, 'investigacion') && (str_contains($header, 'proyecto') || str_contains($header, 'memoria'))) {
                $map['col_proyecto_investigacion'] = $idx;
                $hasInvestigacion = true;
            }

            if (str_contains($header, 'interaccion') && (str_contains($header, 'social') || str_contains($header, 'proyecto'))) {
                $map['col_proyecto_interaccion'] = $idx;
                $hasInteraccion = true;
            }

            if ($header === 'titulo' || str_contains($header, 'titulo de la') || str_contains($header, 'nombre del proyecto')) {
                $map['col_titulo'] = $idx;
                $hasTitulo = true;
            }

            if (str_contains($header, 'gestion') || str_contains($header, 'anio') || $header === 'ano' || $header === 'year') {
                $map['col_gestion'] = $idx;
            }

            if (str_contains($header, 'etiqueta') || str_contains($header, 'categoria') || $header === 'tipo') {
                $map['col_etiqueta'] = $idx;
            }

            if (str_contains($header, 'observacion') || str_contains($header, 'archivo') || str_contains($header, 'resolucion')) {
                $map['col_observaciones'] = $idx;
                $map['col_descripcion'] = $idx;
            } elseif (str_contains($header, 'descripcion') || str_contains($header, 'resumen') || str_contains($header, 'abstract')) {
                $map['col_descripcion'] = $idx;
            }

            if (str_contains($header, 'enlace') || str_contains($header, 'url') || str_contains($header, 'drive') || str_contains($header, 'link')) {
                $map['col_enlace_url'] = $idx;
            }
        }

        // Formato Institucional (columnas separadas de investigación e interacción)
        if ($hasDocente && ($hasInvestigacion || $hasInteraccion)) {
            $map['tipo'] = 'institucional';

            return $map;
        }

        // Formato Plantilla Estándar
        if ($hasTitulo && ($hasDocente || isset($map['col_autor']))) {
            $map['tipo'] = 'standard';

            return $map;
        }

        return null;
    }

    /**
     * Procesa una fila de datos de acuerdo al formato detectado.
     */
    protected function processDataRow(
        array $cells,
        array $headerMap,
        ?int $sheetGestion,
        ?string $resolucionContext,
        ?string $seccionActual,
        int $rowNumber
    ): void {
        $autorRaw = trim($cells[$headerMap['col_autor'] ?? -1] ?? '');

        // Ignorar filas de separadores, subtítulos o totales
        if (empty($autorRaw) || (is_numeric($autorRaw) && count($cells) <= 2)) {
            return;
        }

        if (preg_match('/^(total|resumen|carrera|ieta)\b/i', $autorRaw)) {
            return;
        }

        // Extraer año de la resolución en la fila si viene explícito (ej: 885/2018, 523/2019, 1049/2024)
        $rowText = implode(' ', $cells);
        $anioFila = $this->extraerAnioDeTexto($rowText) ?? $sheetGestion ?? (int) date('Y');

        if (isset($headerMap['col_gestion']) && ! empty($cells[$headerMap['col_gestion']])) {
            $colGestionVal = trim($cells[$headerMap['col_gestion']]);
            if (is_numeric($colGestionVal) && (int) $colGestionVal >= 1900 && (int) $colGestionVal <= 2100) {
                $anioFila = (int) $colGestionVal;
            }
        }

        // Caso A: Formato Institucional (Proyectos de Investigación y/o Interacción Social)
        if ($headerMap['tipo'] === 'institucional') {
            $colInvIdx = $headerMap['col_proyecto_investigacion'] ?? 2;
            $colIntIdx = $headerMap['col_proyecto_interaccion'] ?? 3;
            $colObsIdx = $headerMap['col_observaciones'] ?? $headerMap['col_descripcion'] ?? 4;

            $tituloInvestigacion = trim($cells[$colInvIdx] ?? '');
            $tituloInteraccion = trim($cells[$colIntIdx] ?? '');
            $observaciones = trim($cells[$colObsIdx] ?? '');

            // Heurística de inversión de columnas / notas de resolución en lugar de proyecto:
            // Ocurre cuando en la celda de proyectos se colocó la nota de resolución (ej: "Resolución HCF No. 885/2018...")
            // y en la celda de autor se colocó el nombre del proyecto o curso institucional (ej: "Gramática de Gráficos...").
            $notaResolucion = $this->obtenerNotaResolucion($tituloInvestigacion, $tituloInteraccion, $observaciones);

            if ($notaResolucion !== null && $this->esTituloDeProyectoOCurso($autorRaw)) {
                $esInteraccion = (! empty($tituloInteraccion) && empty($tituloInvestigacion))
                    || str_starts_with(Str::ascii(mb_strtolower($autorRaw)), 'curso')
                    || str_starts_with(Str::ascii(mb_strtolower($autorRaw)), 'taller')
                    || str_starts_with(Str::ascii(mb_strtolower($autorRaw)), 'difusion');

                $etiqueta = $esInteraccion ? 'Proyecto de Interacción Social' : 'Proyecto de Investigación';

                $this->procesarProyectoInstitucionalInvertido(
                    $autorRaw,
                    $notaResolucion,
                    $anioFila,
                    $seccionActual,
                    $etiqueta,
                    $rowNumber
                );

                return;
            }

            // Flujo regular con docente e investigadores identificados
            $partesAutores = preg_split('/[\n\r;,]+/', $autorRaw);
            $partesAutores = array_values(array_filter(array_map('trim', $partesAutores)));
            $autorPrincipalRaw = $partesAutores[0] ?? $autorRaw;
            $coautores = array_slice($partesAutores, 1);

            $autor = $this->resolverOCrearAutor($autorPrincipalRaw);
            if (! $autor) {
                $this->errors[] = "Hoja '{$this->currentSheetName}', Fila {$rowNumber}: No se pudo determinar el docente/investigador '{$autorRaw}'.";
                $this->skippedCount++;

                return;
            }

            // Construir descripción complementaria con contexto institucional
            $descComplementaria = [];
            if ($resolucionContext) {
                $descComplementaria[] = "Aprobado con {$resolucionContext}.";
            }
            if ($seccionActual) {
                $descComplementaria[] = "Ámbito: {$seccionActual}.";
            }
            if (! empty($coautores)) {
                $descComplementaria[] = 'Coautores: '.implode(', ', $coautores).'.';
            }
            if ($observaciones) {
                $descComplementaria[] = "Observaciones de archivo: {$observaciones}.";
            }
            $descripcionFinal = ! empty($descComplementaria) ? implode(' ', $descComplementaria) : null;

            // 1. Guardar Proyecto de Investigación si existe
            if (! empty($tituloInvestigacion)) {
                $this->guardarOActualizarMemoria([
                    'autor_id' => $autor->id,
                    'mdi_titulo' => $tituloInvestigacion,
                    'mdi_descripcion' => $descripcionFinal,
                    'mdi_etiqueta' => 'Proyecto de Investigación',
                    'mdi_anio' => $anioFila,
                    'mdi_enlace_url' => null,
                ], $rowNumber);
            }

            // 2. Guardar Proyecto de Interacción Social si existe
            if (! empty($tituloInteraccion)) {
                $this->guardarOActualizarMemoria([
                    'autor_id' => $autor->id,
                    'mdi_titulo' => $tituloInteraccion,
                    'mdi_descripcion' => $descripcionFinal,
                    'mdi_etiqueta' => 'Proyecto de Interacción Social',
                    'mdi_anio' => $anioFila,
                    'mdi_enlace_url' => null,
                ], $rowNumber);
            }

            return;
        }

        // Caso B: Formato Estándar (Plantilla única con columna 'titulo')
        $titulo = trim($cells[$headerMap['col_titulo']] ?? '');
        if (empty($titulo)) {
            $this->errors[] = "Hoja '{$this->currentSheetName}', Fila {$rowNumber}: Título omitido o vacío.";
            $this->skippedCount++;

            return;
        }

        $autor = $this->resolverOCrearAutor($autorRaw);
        if (! $autor) {
            $autor = $this->obtenerAutorInstitucional('IETA');
        }

        $etiqueta = trim($cells[$headerMap['col_etiqueta'] ?? -1] ?? '') ?: 'Proyecto de Investigación';
        $descripcion = trim($cells[$headerMap['col_descripcion'] ?? -1] ?? '');

        $enlaceUrl = trim($cells[$headerMap['col_enlace_url'] ?? -1] ?? '');
        if (! empty($enlaceUrl) && ! filter_var($enlaceUrl, FILTER_VALIDATE_URL)) {
            $enlaceUrl = null;
        }

        $this->guardarOActualizarMemoria([
            'autor_id' => $autor->id,
            'mdi_titulo' => $titulo,
            'mdi_descripcion' => $descripcion ?: null,
            'mdi_etiqueta' => $etiqueta,
            'mdi_anio' => $anioFila,
            'mdi_enlace_url' => $enlaceUrl ?: null,
        ], $rowNumber);
    }

    /**
     * Determina si alguna celda contiene una nota institucional de resolución en lugar de un título de proyecto.
     */
    protected function obtenerNotaResolucion(string ...$textos): ?string
    {
        foreach ($textos as $t) {
            $ascii = Str::ascii(mb_strtolower(trim($t)));
            if (empty($ascii)) {
                continue;
            }

            if ((str_contains($ascii, 'resolucion') || str_contains($ascii, 'hcf'))
                && (str_contains($ascii, 'nota:')
                    || str_contains($ascii, 'aprobacion de perfil')
                    || str_contains($ascii, 'aprobacion de informe')
                    || str_contains($ascii, 'informe final')
                    || str_contains($ascii, 'solo perfil')
                    || str_contains($ascii, 'solo hay perfil'))) {
                return trim($t);
            }
        }

        return null;
    }

    /**
     * Evalúa si una cadena corresponde más a un título de proyecto o curso que al nombre de una persona.
     */
    protected function esTituloDeProyectoOCurso(string $cadena): bool
    {
        $cadena = trim($cadena);
        if (empty($cadena)) {
            return false;
        }

        // Si inicia con títulos o grados académicos de persona
        if (preg_match('/^(lic[a]?\.?|m\.?sc\.?|dr[a]?\.?|ph\.?d\.?|univ\.?|ing\.?)\s+/i', $cadena)) {
            return false;
        }

        $ascii = Str::ascii(mb_strtolower($cadena));

        // Patrones típicos de títulos de proyectos, investigaciones o cursos
        $palabrasClaveProyecto = [
            'analisis', 'curso', 'taller', 'sistema', 'plan', 'estudio', 'estimacion',
            'implementacion', 'difusion', 'modelos', 'gestion', 'evaluacion', 'rediseno',
            'olimpiada', 'investigacion', 'proyecto', 'regularizacion', 'dossier',
            'ciencia de datos', 'redes bayesianas', 'visualizacion', 'estadistica',
            'metodos', 'diagnostico', 'comparacion', 'reacreditacion', 'censo',
        ];

        foreach ($palabrasClaveProyecto as $kw) {
            if (str_starts_with($ascii, $kw) || str_contains($ascii, " {$kw} ")) {
                return true;
            }
        }

        // Si la cadena tiene más de 45 caracteres y varias palabras, generalmente es un título
        if (mb_strlen($cadena) > 45 && str_word_count($cadena) > 5) {
            return true;
        }

        return false;
    }

    /**
     * Procesa proyectos donde las columnas del Excel vinieron invertidas o con notas de resolución globales.
     */
    protected function procesarProyectoInstitucionalInvertido(
        string $tituloProyecto,
        string $notaResolucion,
        int $anio,
        ?string $seccionActual,
        string $etiqueta,
        int $rowNumber
    ): void {
        $autorInstitucional = $this->obtenerAutorInstitucional('IETA');

        // Determinar año específico a partir del número de resolución si está disponible
        $anioResolucion = $this->extraerAnioDeTexto($notaResolucion);
        $anioFinal = $anioResolucion ?? $anio;

        // Construir descripción enriquecida
        $descParts = [];
        $descParts[] = $notaResolucion;
        if ($seccionActual) {
            $descParts[] = "Ámbito: {$seccionActual}.";
        }
        $descripcion = implode(' ', $descParts);

        $this->guardarOActualizarMemoria([
            'autor_id' => $autorInstitucional->id,
            'mdi_titulo' => $tituloProyecto,
            'mdi_descripcion' => $descripcion,
            'mdi_etiqueta' => $etiqueta,
            'mdi_anio' => $anioFinal,
            'mdi_enlace_url' => null,
        ], $rowNumber);
    }

    /**
     * Extrae un año de 4 dígitos de un texto de resolución o encabezado (ej. "885/2018" -> 2018).
     */
    protected function extraerAnioDeTexto(string $texto): ?int
    {
        if (preg_match('/(?:hcf|resoluci[oó]n)[^\d]*n[oº°]?\s*[\d\.\-]+\s*\/\s*(19\d\d|20\d\d)\b/iu', $texto, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/\b(19\d\d|20\d\d)\b/', $texto, $matches)) {
            $val = (int) $matches[1];
            if ($val >= 2000 && $val <= 2040) {
                return $val;
            }
        }

        return null;
    }

    /**
     * Guarda un nuevo registro o actualiza si ya existía para evitar duplicados exactos.
     */
    protected function guardarOActualizarMemoria(array $data, int $rowNumber = 0): void
    {
        try {
            DB::transaction(function () use ($data) {
                $tituloNormalizado = mb_strtolower(trim($data['mdi_titulo']));

                $existente = MemoriasInvestigacion::where('autor_id', $data['autor_id'])
                    ->where('mdi_anio', $data['mdi_anio'])
                    ->whereRaw('LOWER(TRIM(mdi_titulo)) = ?', [$tituloNormalizado])
                    ->first();

                if ($existente) {
                    $existente->update([
                        'mdi_descripcion' => $data['mdi_descripcion'] ?? $existente->mdi_descripcion,
                        'mdi_etiqueta' => $data['mdi_etiqueta'] ?? $existente->mdi_etiqueta,
                        'mdi_enlace_url' => $data['mdi_enlace_url'] ?? $existente->mdi_enlace_url,
                    ]);
                } else {
                    MemoriasInvestigacion::create($data);
                }

                $this->importedCount++;
            });
        } catch (Throwable $e) {
            $hojaRef = $this->currentSheetName ? "Hoja '{$this->currentSheetName}', " : '';
            $filaRef = $rowNumber > 0 ? "Fila {$rowNumber}: " : '';
            $this->errors[] = "{$hojaRef}{$filaRef}Error al guardar '{$data['mdi_titulo']}': {$e->getMessage()}";
            $this->skippedCount++;
        }
    }

    /**
     * Obtiene o crea el autor institucional para proyectos del IETA o entidad académica.
     */
    protected function obtenerAutorInstitucional(string $nombre = 'IETA'): Autor
    {
        $persona = Persona::firstOrCreate(
            ['p_correo' => 'ieta@fcpn.edu.bo'],
            [
                'p_nombre' => 'Instituto de Estadística Teórica y Aplicada',
                'p_apellido_paterno' => 'IETA',
                'p_apellido_materno' => '',
            ]
        );

        $tipoAutor = TipoAutor::firstOrCreate(
            ['ta_nombre' => 'Institucional'],
            ['ta_descripcion' => 'Autor institucional o entidad académica', 'ta_estado' => true]
        );

        $grado = GradoAcademico::firstOrCreate(
            ['ga_abreviatura' => 'Inst.'],
            ['ga_nombre' => 'Institucional', 'ga_estado' => true]
        );

        $slug = 'ieta-instituto-de-estadistica-teorica-y-aplicada';

        return Autor::firstOrCreate(
            ['persona_id' => $persona->id],
            [
                'tipo_autor_id' => $tipoAutor->id,
                'grado_academico_id' => $grado->id,
                'at_slug' => $slug,
                'at_estado' => true,
            ]
        );
    }

    /**
     * Resuelve un autor existente por coincidencia de nombre o correo, o crea un registro básico.
     */
    protected function resolverOCrearAutor(string $cadenaAutor): ?Autor
    {
        $cadenaAutor = trim($cadenaAutor);
        if (empty($cadenaAutor)) {
            return null;
        }

        // Si se proporcionó un ID numérico directo
        if (is_numeric($cadenaAutor)) {
            $autorPorId = Autor::find((int) $cadenaAutor);
            if ($autorPorId) {
                return $autorPorId;
            }
        }

        // 1. Detectar Grado Académico por prefijo
        $gradoAbrev = 'Lic.';
        $gradoNombre = 'Licenciatura';
        if (preg_match('/^(m\.?sc\.?|mag[ií]ster|master)\b/i', $cadenaAutor)) {
            $gradoAbrev = 'M.Sc.';
            $gradoNombre = 'Magister Scientiarum';
        } elseif (preg_match('/^(dr[a]?\.?|doctor[a]?|ph\.?d\.?)\b/i', $cadenaAutor)) {
            $gradoAbrev = 'Dr.';
            $gradoNombre = 'Doctorado';
        } elseif (preg_match('/^(univ\.?|estudiante)\b/i', $cadenaAutor)) {
            $gradoAbrev = 'Univ.';
            $gradoNombre = 'Universitario';
        } elseif (preg_match('/^(ing\.?|ingenier[oa]?)\b/i', $cadenaAutor)) {
            $gradoAbrev = 'Ing.';
            $gradoNombre = 'Ingeniería';
        }

        // 2. Limpiar prefijos académicos del nombre
        $nombreLimpio = preg_replace('/^(lic[a]?\.?|m\.?sc\.?|dr[a]?\.?|ph\.?d\.?|univ\.?|ing\.?)\s+/i', '', $cadenaAutor);
        $nombreLimpio = trim((string) $nombreLimpio);
        if (empty($nombreLimpio)) {
            $nombreLimpio = $cadenaAutor;
        }

        // 3. Buscar persona existente por nombre completo (sin acentos)
        $personas = Persona::with('autor')->get();
        $targetAscii = Str::ascii(mb_strtolower($nombreLimpio));

        foreach ($personas as $p) {
            $fullName = Str::ascii(mb_strtolower(trim("{$p->p_nombre} {$p->p_apellido_paterno} {$p->p_apellido_materno}")));
            $shortName = Str::ascii(mb_strtolower(trim("{$p->p_nombre} {$p->p_apellido_paterno}")));

            if ($fullName === $targetAscii || $shortName === $targetAscii) {
                if ($p->autor) {
                    return $p->autor;
                }

                return $this->crearAutorParaPersona($p, $gradoAbrev, $gradoNombre);
            }

            // Coincidencia parcial si contiene nombre y apellido paterno
            if (str_contains($fullName, $targetAscii) || str_contains($targetAscii, $shortName)) {
                if ($p->autor) {
                    return $p->autor;
                }

                return $this->crearAutorParaPersona($p, $gradoAbrev, $gradoNombre);
            }
        }

        // 4. Si no existe, crear Persona y Autor automáticamente
        $partes = preg_split('/\s+/', $nombreLimpio);
        $numPartes = count($partes);

        if ($numPartes === 1) {
            $pNombre = $partes[0];
            $pPaterno = 'Investigador';
            $pMaterno = null;
        } elseif ($numPartes === 2) {
            $pNombre = $partes[0];
            $pPaterno = $partes[1];
            $pMaterno = null;
        } elseif ($numPartes === 3) {
            $pNombre = $partes[0];
            $pPaterno = $partes[1];
            $pMaterno = $partes[2];
        } else {
            // 4 o más palabras: ej "Juan Carlos Flores López" -> p_nombre: "Juan Carlos", paterno: "Flores", materno: "López"
            $pMaterno = array_pop($partes);
            $pPaterno = array_pop($partes);
            $pNombre = implode(' ', $partes);
        }

        // Generar correo único
        $correoBase = Str::slug("{$pNombre}.{$pPaterno}").'@ieta.umsa.bo';
        $correo = $correoBase;
        $counter = 1;
        while (Persona::where('p_correo', $correo)->exists() || User::where('email', $correo)->exists()) {
            $correo = Str::slug("{$pNombre}.{$pPaterno}")."{$counter}@ieta.umsa.bo";
            $counter++;
        }

        $persona = Persona::create([
            'p_nombre' => $pNombre,
            'p_apellido_paterno' => $pPaterno,
            'p_apellido_materno' => $pMaterno,
            'p_correo' => $correo,
        ]);

        return $this->crearAutorParaPersona($persona, $gradoAbrev, $gradoNombre);
    }

    protected function crearAutorParaPersona(Persona $persona, string $gradoAbrev, string $gradoNombre): Autor
    {
        $grado = GradoAcademico::firstOrCreate(
            ['ga_abreviatura' => $gradoAbrev],
            ['ga_nombre' => $gradoNombre, 'ga_estado' => true]
        );

        $tipoAutor = TipoAutor::firstOrCreate(
            ['ta_nombre' => 'Docente Investigador'],
            ['ta_descripcion' => 'Docente e Investigador Académico', 'ta_estado' => true]
        );

        $baseSlug = Str::slug("{$persona->p_nombre}-{$persona->p_apellido_paterno}");
        $slug = $baseSlug ?: 'autor-'.uniqid();
        $counter = 1;
        while (Autor::where('at_slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return Autor::create([
            'persona_id' => $persona->id,
            'tipo_autor_id' => $tipoAutor->id,
            'grado_academico_id' => $grado->id,
            'at_slug' => $slug,
            'at_estado' => true,
        ]);
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
