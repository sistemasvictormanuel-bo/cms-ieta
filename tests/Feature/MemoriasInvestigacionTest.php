<?php

use App\Exports\MemoriasInvestigacionPlantillaExport;
use App\Imports\MemoriasInvestigacionImport;
use App\Models\Autor;
use App\Models\GradoAcademico;
use App\Models\MemoriasInvestigacion;
use App\Models\Persona;
use App\Models\TipoAutor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
});

function crearAutorPrueba(): Autor
{
    $tipoAutor = TipoAutor::firstOrCreate(['ta_nombre' => 'Docente Investigador'], ['ta_descripcion' => 'Investigador']);
    $grado = GradoAcademico::firstOrCreate(['ga_nombre' => 'Magister'], ['ga_abreviatura' => 'M.Sc.', 'ga_estado' => true]);
    $persona = Persona::create([
        'p_nombre' => 'Nicolás',
        'p_apellido_paterno' => 'Chávez',
        'p_apellido_materno' => 'Quisbert',
        'p_correo' => 'nchavez_test@umsa.bo',
    ]);

    return Autor::create([
        'persona_id' => $persona->id,
        'tipo_autor_id' => $tipoAutor->id,
        'grado_academico_id' => $grado->id,
        'at_slug' => 'nicolas-chavez-'.uniqid(),
        'at_estado' => true,
    ]);
}

it('permite a un administrador listar las memorias de investigacion', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->get(route('admin.memorias-investigacion.index'));

    $response->assertStatus(200);
});

it('permite registrar una memoria con anio o gestion', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');
    $autor = crearAutorPrueba();

    $payload = [
        'autor_id' => $autor->id,
        'mdi_titulo' => 'Proyecto de Investigación Gestión 2015',
        'mdi_descripcion' => 'Aprobado con Resolución del HCF Nº 780/2015',
        'mdi_etiqueta' => 'Proyecto de Investigación',
        'mdi_anio' => 2015,
        'mdi_enlace_url' => 'https://drive.google.com/test',
    ];

    $response = $this->actingAs($admin)->post(route('admin.memorias-investigacion.store'), $payload);

    $response->assertRedirect(route('admin.memorias-investigacion.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Proyecto de Investigación Gestión 2015',
        'mdi_anio' => 2015,
    ]);
});

it('convierte mdi_fecha a mdi_anio automaticamente para mantener compatibilidad', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');
    $autor = crearAutorPrueba();

    $payload = [
        'autor_id' => $autor->id,
        'mdi_titulo' => 'Memoria de Prueba Compatibilidad',
        'mdi_descripcion' => 'Abstract de prueba',
        'mdi_etiqueta' => 'Memoria Anual',
        'mdi_fecha' => '2023-11-15',
    ];

    $response = $this->actingAs($admin)->post(route('admin.memorias-investigacion.store'), $payload);

    $response->assertRedirect(route('admin.memorias-investigacion.index'));

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Memoria de Prueba Compatibilidad',
        'mdi_anio' => 2023,
    ]);
});

it('permite actualizar el anio o gestion de una memoria', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');
    $autor = crearAutorPrueba();

    $memoria = MemoriasInvestigacion::create([
        'autor_id' => $autor->id,
        'mdi_titulo' => 'Investigación Inicial',
        'mdi_etiqueta' => 'Proyecto de Investigación',
        'mdi_anio' => 2018,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.memorias-investigacion.update', $memoria->id), [
        'autor_id' => $autor->id,
        'mdi_titulo' => 'Investigación Actualizada',
        'mdi_etiqueta' => 'Proyecto de Investigación',
        'mdi_anio' => 2024,
    ]);

    $response->assertRedirect(route('admin.memorias-investigacion.index'));

    $memoria->refresh();
    expect($memoria->mdi_anio)->toBe(2024);
    expect($memoria->mdi_titulo)->toBe('Investigación Actualizada');
});

it('permite eliminar una memoria de investigacion', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');
    $autor = crearAutorPrueba();

    $memoria = MemoriasInvestigacion::create([
        'autor_id' => $autor->id,
        'mdi_titulo' => 'Investigación para Eliminar',
        'mdi_etiqueta' => 'Borrador',
        'mdi_anio' => 2020,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.memorias-investigacion.destroy', $memoria->id));

    $response->assertRedirect(route('admin.memorias-investigacion.index'));
    $this->assertDatabaseMissing('memorias_investigaciones', [
        'id' => $memoria->id,
    ]);
});

it('permite descargar la plantilla excel de memorias de investigacion', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->get(route('admin.memorias-investigacion.plantilla'));

    $response->assertStatus(200);
    expect($response->headers->get('content-disposition'))->toContain('plantilla_memorias_investigacion.xlsx');
});

it('permite exportar el listado de memorias de investigacion a excel', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->get(route('admin.memorias-investigacion.export'));

    $response->assertStatus(200);
    expect($response->headers->get('content-disposition'))->toContain('memorias_investigacion_export_');
});

it('importa registros desde coleccion con formato estandar y crea autores automaticamente', function () {
    $import = new MemoriasInvestigacionImport;

    $rows = collect([
        // Fila 1: encabezados estándar
        collect(['titulo', 'autor', 'gestion', 'etiqueta', 'descripcion', 'enlace_url']),
        // Fila 2: datos
        collect([
            'Medición de la Disparidad Salarial en La Paz',
            'Lic. Melisa Abalos Choque',
            '2015',
            'Proyecto de Investigación',
            'Proyecto en físico en archivos',
            'https://drive.google.com/test-salarios',
        ]),
    ]);

    $import->collection($rows);

    expect($import->getImportedCount())->toBe(1);
    expect($import->getErrors())->toBeEmpty();

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Medición de la Disparidad Salarial en La Paz',
        'mdi_anio' => 2015,
        'mdi_etiqueta' => 'Proyecto de Investigación',
    ]);

    // Verificar que se haya creado el autor
    $this->assertDatabaseHas('personas', [
        'p_nombre' => 'Melisa',
        'p_apellido_paterno' => 'Abalos',
    ]);
});

it('importa registros desde formato institucional con columnas separadas de investigacion e interaccion', function () {
    $import = new MemoriasInvestigacionImport;

    $rows = collect([
        // Filas superiores institucionales
        collect(['PROYECTOS DE INVESTIGACIÓN E INTERACCIÓN SOCIAL']),
        collect(['GESTIÓN 2015']),
        collect(['Aprobado con Resolución del HCF Nº 780/2015']),
        collect(['PROYECTOS DE LA CARRERA:']),
        // Fila de encabezados de tabla
        collect(['Nº', 'NOMBRE DEL DOCENTE', 'PROYECTO DE INVESTIGACIÓN', 'PROYECTO DE INTERACCIÓN SOCIAL', 'OBSERVACIONES EN ARCHIVO']),
        // Fila 1: Docente con ambos proyectos
        collect([
            '2',
            'M.Sc. Nicolás Chávez Quisbert',
            'Analisis estadistico del crecimiento poblacional estudiantil en la Universidad Mayor de San Andres',
            'Curso Computacional modelación y Simulación de Series de Tiempo Univariadas.',
            'Se cuenta los dos proyectos en fisico',
        ]),
        // Fila 2: Docente con solo proyecto de interacción
        collect([
            '5',
            'Lic. Juan Carlos Flores López, M.Sc. Fernando Rivero',
            '',
            'Promoción de la Carrera de Estadística',
            'Proyecto de interaccion existente en los archivos',
        ]),
    ]);

    $import->collection($rows);

    // Debe haber importado:
    // Nicolás Chávez: 1 proyecto investigación + 1 proyecto interacción
    // Juan Carlos Flores: 1 proyecto interacción
    // Total = 3
    expect($import->getImportedCount())->toBe(3);

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Analisis estadistico del crecimiento poblacional estudiantil en la Universidad Mayor de San Andres',
        'mdi_anio' => 2015,
        'mdi_etiqueta' => 'Proyecto de Investigación',
    ]);

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Curso Computacional modelación y Simulación de Series de Tiempo Univariadas.',
        'mdi_anio' => 2015,
        'mdi_etiqueta' => 'Proyecto de Interacción Social',
    ]);

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Promoción de la Carrera de Estadística',
        'mdi_anio' => 2015,
        'mdi_etiqueta' => 'Proyecto de Interacción Social',
    ]);
});

it('valida la subida de archivo excel en el endpoint de importacion', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->post(route('admin.memorias-investigacion.import'), []);

    $response->assertSessionHasErrors(['archivo_excel']);
});

it('permite importar exitosamente un archivo excel real mediante la peticion http', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    // Generar archivo Excel real en memoria usando la plantilla
    $export = new MemoriasInvestigacionPlantillaExport;
    $tempFile = tempnam(sys_get_temp_dir(), 'test_mem_').'.xlsx';
    Excel::store($export, basename($tempFile), 'local');
    $storedPath = Storage::disk('local')->path(basename($tempFile));

    $uploadedFile = new UploadedFile(
        $storedPath,
        'plantilla_memorias.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($admin)->post(route('admin.memorias-investigacion.import'), [
        'archivo_excel' => $uploadedFile,
    ]);

    $response->assertRedirect(route('admin.memorias-investigacion.index'));
    $response->assertSessionHas('success');

    // Debe contener las filas de la plantilla
    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Análisis estadístico del crecimiento poblacional estudiantil en la Universidad Mayor de San Andrés',
        'mdi_anio' => 2015,
    ]);

    // Limpiar archivo temporal
    Storage::disk('local')->delete(basename($tempFile));
});

it('importa correctamente proyectos institucionales con notas de resolucion largas y extraccion de anio', function () {
    $import = new MemoriasInvestigacionImport;

    $notaResolucionLarga = 'Resolución HCF No. 885/2018 (aprobación de perfiles). Nota: solo se cuenta con la resolución de aprobación de los perfiles; no existe la resolución que apruebe el informe final de los proyectos. Con Informe Final los proyectos de investigación. Proyecto de Interacción Social solo hay perfil.';

    $rows = collect([
        collect(['PROYECTOS DE INVESTIGACIÓN E INTERACCIÓN SOCIAL']),
        collect(['GESTIÓN 2018']),
        collect(['ROYECTOS DEL IETA']),
        collect(['Nº', 'NOMBRE DEL DOCENTE', 'PROYECTO DE INVESTIGACIÓN', 'PROYECTO DE INTERACCIÓN SOCIAL']),
        collect([
            '1',
            'Analisis Estadístico para la investigación socioeconómicos',
            $notaResolucionLarga,
            '',
        ]),
        collect([
            '2',
            'Curso de introducción a Matlab',
            '',
            $notaResolucionLarga,
        ]),
    ]);

    $import->collection($rows);

    expect($import->getImportedCount())->toBe(2);
    expect($import->getErrors())->toBeEmpty();

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Analisis Estadístico para la investigación socioeconómicos',
        'mdi_anio' => 2018,
        'mdi_etiqueta' => 'Proyecto de Investigación',
    ]);

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => 'Curso de introducción a Matlab',
        'mdi_anio' => 2018,
        'mdi_etiqueta' => 'Proyecto de Interacción Social',
    ]);
});

it('permite guardar memorias con titulos de mas de 255 caracteres sin error de truncado', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');
    $autor = crearAutorPrueba();

    $tituloLargo = 'Investigación Integral sobre Métodos Multivariantes Avanzados y Modelos Lineales Generalizados Aplicados a Series de Tiempo de Gran Escala en la Población Estudiantil y Docente de la Universidad Mayor de San Andrés durante las Gestiones Académicas Correspondientes al Período 2015-2025';

    expect(mb_strlen($tituloLargo))->toBeGreaterThan(255);

    $payload = [
        'autor_id' => $autor->id,
        'mdi_titulo' => $tituloLargo,
        'mdi_descripcion' => 'Descripción de prueba con título largo',
        'mdi_etiqueta' => 'Proyecto de Investigación',
        'mdi_anio' => 2024,
    ];

    $response = $this->actingAs($admin)->post(route('admin.memorias-investigacion.store'), $payload);

    $response->assertRedirect(route('admin.memorias-investigacion.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('memorias_investigaciones', [
        'mdi_titulo' => $tituloLargo,
        'mdi_anio' => 2024,
    ]);
});
