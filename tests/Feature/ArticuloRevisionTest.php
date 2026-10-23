<?php

use App\Models\Articulo;
use App\Models\Autor;
use App\Models\Especialidades;
use App\Models\GradoAcademico;
use App\Models\Persona;
use App\Models\TipoArticulo;
use App\Models\TipoAutor;
use App\Models\TipoParticipacion;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'autor', 'guard_name' => 'web']);
});

it('permite ver detalle de articulo con autores y especialidades sin error de relacion', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $tipo = TipoArticulo::firstOrCreate(['tar_nombre' => 'Artículo Original'], ['tar_descripcion' => 'Original', 'tar_estado' => true]);
    $tipoAutor = TipoAutor::firstOrCreate(['ta_nombre' => 'Principal'], ['ta_descripcion' => 'Autor Principal']);
    $grado = GradoAcademico::firstOrCreate(['ga_nombre' => 'Doctor'], ['ga_abreviatura' => 'Dr.', 'ga_estado' => true]);
    $persona = Persona::firstOrCreate(
        ['p_correo_institucional' => 'autor_test@ieta.edu.bo'],
        ['p_nombre' => 'Juan', 'p_apellido_paterno' => 'Perez', 'p_genero' => 'M', 'p_ci' => '1234567']
    );
    $autor = Autor::firstOrCreate(
        ['persona_id' => $persona->id],
        [
            'tipo_autor_id' => $tipoAutor->id,
            'grado_academico_id' => $grado->id,
            'at_slug' => 'juan-perez',
            'at_estado' => true,
        ]
    );

    $especialidad = Especialidades::firstOrCreate(['ep_nombre' => 'Bioestadística'], ['ep_descripcion' => 'Bio', 'ep_estado' => true]);
    $autor->especialidades()->syncWithoutDetaching([$especialidad->id]);

    $articulo = Articulo::create([
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Estudio de Prueba Especialidades',
        'art_resumen' => 'Resumen de prueba',
        'art_fecha_recepcion' => now(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
        'created_by_user_id' => $admin->id,
    ]);

    $participacion = TipoParticipacion::firstOrCreate(['tp_nombre' => 'Autor Correspondiente']);
    $articulo->autores()->attach($autor->id, ['tipo_participaciones_id' => $participacion->id]);

    $response = $this->actingAs($admin)->get(route('admin.articulos.show', $articulo->id));

    $response->assertStatus(200);
});

it('permite a un administrador aprobar un articulo pendiente y autorellena fecha de aceptacion', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $tipo = TipoArticulo::firstOrCreate(['tar_nombre' => 'Artículo Original'], ['tar_descripcion' => 'Original', 'tar_estado' => true]);

    $articulo = Articulo::create([
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Articulo Para Aprobar Test',
        'art_resumen' => 'Resumen',
        'art_fecha_recepcion' => now()->subDays(5)->toDateString(),
        'art_fecha_aceptado' => null,
        'estado_revision' => 'pendiente',
        'art_estado' => false,
        'created_by_user_id' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.articulos.aprobar', $articulo->id), [
        'motivo' => 'Cumple con los requisitos editoriales.',
    ]);

    $response->assertRedirect();
    $articuloActualizado = $articulo->fresh();
    expect($articuloActualizado->estado_revision)->toBe('aprobado');
    expect($articuloActualizado->art_estado)->toBeTrue();
    expect($articuloActualizado->art_fecha_aceptado->toDateString())->toBe(now()->toDateString());
});

it('un autor al registrar un articulo no fija fecha de aceptacion y su recepcion es automatica', function () {
    $autorUser = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_confirmed_at' => now(),
    ]);
    $autorUser->assignRole('autor');

    $persona = Persona::firstOrCreate(
        ['p_correo_institucional' => 'autor_envio@ieta.edu.bo'],
        ['p_nombre' => 'Mario', 'p_apellido_paterno' => 'Gomez', 'p_genero' => 'M', 'p_ci' => '7654321']
    );
    $tipoAutor = TipoAutor::firstOrCreate(['ta_nombre' => 'Coautor'], ['ta_descripcion' => 'Coautor']);
    $grado = GradoAcademico::firstOrCreate(['ga_nombre' => 'Magister'], ['ga_abreviatura' => 'MSc.', 'ga_estado' => true]);
    $autor = Autor::firstOrCreate(
        ['persona_id' => $persona->id],
        [
            'tipo_autor_id' => $tipoAutor->id,
            'grado_academico_id' => $grado->id,
            'at_slug' => 'mario-gomez',
            'at_estado' => true,
        ]
    );

    $tipo = TipoArticulo::firstOrCreate(['tar_nombre' => 'Artículo Original'], ['tar_descripcion' => 'Original', 'tar_estado' => true]);
    $part = TipoParticipacion::firstOrCreate(['tp_nombre' => 'Autor Principal']);

    $payload = [
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Articulo Enviado Por Autor Test',
        'art_resumen' => 'Resumen de prueba enviado por autor',
        'art_fecha_recepcion' => '2020-01-01', // Intento de fecha anterior
        'art_fecha_aceptado' => '2026-12-31', // El autor no debe poder fijar aceptación
        'autores' => [
            ['autor_id' => $autor->id, 'tipo_participaciones_id' => $part->id],
        ],
    ];

    $response = $this->actingAs($autorUser)->post(route('admin.articulos.store'), $payload);

    $response->assertRedirect();

    $articuloCreado = Articulo::where('art_titulo', 'Articulo Enviado Por Autor Test')->first();
    expect($articuloCreado)->not->toBeNull();
    expect($articuloCreado->estado_revision)->toBe('pendiente');
    // Fecha de aceptación debe ser nula hasta que el admin apruebe
    expect($articuloCreado->art_fecha_aceptado)->toBeNull();
    // Fecha de recepción se auto-asigna a la fecha de hoy
    expect($articuloCreado->art_fecha_recepcion->toDateString())->toBe(now()->toDateString());
});

it('permite a un administrador rechazar un articulo con motivo', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $tipo = TipoArticulo::firstOrCreate(['tar_nombre' => 'Artículo Original'], ['tar_descripcion' => 'Original', 'tar_estado' => true]);

    $articulo = Articulo::create([
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Articulo Para Rechazar Test',
        'art_resumen' => 'Resumen',
        'art_fecha_recepcion' => now(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
        'created_by_user_id' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.articulos.rechazar', $articulo->id), [
        'motivo' => 'Se requiere ajustar las referencias en formato APA.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('articulos', [
        'id' => $articulo->id,
        'estado_revision' => 'rechazado',
        'art_estado' => false,
        'revision_motivo' => 'Se requiere ajustar las referencias en formato APA.',
    ]);
});

it('la api publica solo retorna articulos aprobados de un autor excluyendo pendientes y rechazados', function () {
    $tipo = TipoArticulo::firstOrCreate(['tar_nombre' => 'Artículo Original'], ['tar_descripcion' => 'Original', 'tar_estado' => true]);
    $tipoAutor = TipoAutor::firstOrCreate(['ta_nombre' => 'Principal'], ['ta_descripcion' => 'Autor Principal']);
    $grado = GradoAcademico::firstOrCreate(['ga_nombre' => 'Licenciatura'], ['ga_abreviatura' => 'Lic.', 'ga_estado' => true]);
    $persona = Persona::create([
        'p_nombre' => 'Carlos',
        'p_apellido_paterno' => 'Miranda',
        'p_correo' => 'carlos@ieta.edu.bo',
    ]);
    $autor = Autor::create([
        'persona_id' => $persona->id,
        'tipo_autor_id' => $tipoAutor->id,
        'grado_academico_id' => $grado->id,
        'at_slug' => 'carlos-miranda-test',
        'at_estado' => true,
    ]);

    $participacion = TipoParticipacion::firstOrCreate(['tp_nombre' => 'Principal']);

    // 1. Artículo APROBADO
    $artAprobado = Articulo::create([
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Articulo Aprobado 1',
        'art_fecha_recepcion' => now(),
        'estado_revision' => 'aprobado',
        'art_estado' => true,
    ]);
    $artAprobado->autores()->attach($autor->id, ['tipo_participaciones_id' => $participacion->id]);

    // 2. Artículo PENDIENTE
    $artPendiente = Articulo::create([
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Articulo Pendiente 2',
        'art_fecha_recepcion' => now(),
        'estado_revision' => 'pendiente',
        'art_estado' => true,
    ]);
    $artPendiente->autores()->attach($autor->id, ['tipo_participaciones_id' => $participacion->id]);

    // 3. Artículo RECHAZADO
    $artRechazado = Articulo::create([
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Articulo Rechazado 3',
        'art_fecha_recepcion' => now(),
        'estado_revision' => 'rechazado',
        'art_estado' => false,
    ]);
    $artRechazado->autores()->attach($autor->id, ['tipo_participaciones_id' => $participacion->id]);

    // Prueba 1: Endpoint /api/articulos?autor_id={id}
    $resArticulos = $this->getJson("/api/articulos?autor_id={$autor->id}");
    $resArticulos->assertOk();
    $dataArticulos = $resArticulos->json('data');
    expect(count($dataArticulos))->toBe(1);
    expect($dataArticulos[0]['id'])->toBe($artAprobado->id);

    // Prueba 2: Endpoint /api/autores/{id}
    $resAutor = $this->getJson("/api/autores/{$autor->id}");
    $resAutor->assertOk();
    $dataAutorArticulos = $resAutor->json('data.articulos');
    expect(count($dataAutorArticulos))->toBe(1);
    expect($dataAutorArticulos[0]['id'])->toBe($artAprobado->id);
});
