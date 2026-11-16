<?php

use App\Models\Articulo;
use App\Models\EdicionVarianza;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TipoArticulo;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

/*
|--------------------------------------------------------------------------
| 1. FLUJO CRUD Y APROBACIÓN DE ARTÍCULOS CIENTÍFICOS
|--------------------------------------------------------------------------
*/

test('administrador puede crear, editar y eliminar articulos, asi como aprobar y rechazar articulos enviados por autores', function () {
    $admin = User::factory()->withTwoFactor()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $autor = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'secret_autor_flow',
        'two_factor_confirmed_at' => now(),
    ]);
    $autor->assignRole('autor');

    $tipo = TipoArticulo::firstOrCreate(
        ['tar_nombre' => 'Artículo Original'],
        ['tar_descripcion' => 'Original', 'tar_estado' => true]
    );

    // 1. Administrador Crea Artículo Directo (se publica directamente como aprobado)
    $postResponse = $this->actingAs($admin)->post(route('admin.articulos.store'), [
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Análisis Estadístico Multivariante en Bolivia',
        'art_slug' => 'analisis-estadistico-multivariante-en-bolivia',
        'art_resumen' => 'Resumen metodológico y descriptivo del estudio.',
        'art_fecha_recepcion' => now()->toDateString(),
        'art_estado' => true,
    ]);
    $postResponse->assertRedirect();
    $this->assertDatabaseHas('articulos', [
        'art_slug' => 'analisis-estadistico-multivariante-en-bolivia',
        'estado_revision' => 'aprobado',
    ]);

    $articuloAdmin = Articulo::where('art_slug', 'analisis-estadistico-multivariante-en-bolivia')->firstOrFail();

    // 2. Administrador Edita Artículo
    $putResponse = $this->actingAs($admin)->put(route('admin.articulos.update', $articuloAdmin->id), [
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Análisis Estadístico Multivariante en Bolivia - Actualizado',
        'art_slug' => 'analisis-estadistico-multivariante-en-bolivia',
        'art_resumen' => 'Resumen ampliado con nuevos modelos.',
        'art_fecha_recepcion' => now()->toDateString(),
    ]);
    $putResponse->assertRedirect();
    $this->assertDatabaseHas('articulos', [
        'id' => $articuloAdmin->id,
        'art_titulo' => 'Análisis Estadístico Multivariante en Bolivia - Actualizado',
    ]);

    // 3. Administrador Elimina Artículo (SoftDelete institucional)
    $deleteResponse = $this->actingAs($admin)->delete(route('admin.articulos.destroy', $articuloAdmin->id));
    $deleteResponse->assertRedirect(route('admin.articulos.index'));
    $this->assertSoftDeleted('articulos', ['id' => $articuloAdmin->id]);
    expect(Articulo::find($articuloAdmin->id))->toBeNull();

    // 4. Autor envía un artículo para revisión (queda en estado pendiente)
    $this->actingAs($autor)->post(route('admin.articulos.store'), [
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Artículo de Autor Para Aprobación',
        'art_slug' => 'articulo-de-autor-para-aprobacion',
        'art_resumen' => 'Propuesta inicial enviada por autor.',
        'art_fecha_recepcion' => now()->toDateString(),
    ])->assertRedirect();

    $articuloPendiente = Articulo::where('art_slug', 'articulo-de-autor-para-aprobacion')->firstOrFail();
    expect($articuloPendiente->estado_revision)->toBe('pendiente');
    expect($articuloPendiente->art_fecha_aceptado)->toBeNull();

    // 5. Administrador Aprueba el Artículo del autor
    $aprobarResponse = $this->actingAs($admin)->post(route('admin.articulos.aprobar', $articuloPendiente->id), [
        'motivo' => 'Aprobado tras evaluación por pares ciegos.',
    ]);
    $aprobarResponse->assertRedirect();
    $articuloPendiente->refresh();
    expect($articuloPendiente->estado_revision)->toBe('aprobado');
    expect($articuloPendiente->art_estado)->toBeTrue();
    expect($articuloPendiente->art_fecha_aceptado)->not->toBeNull();
    expect($articuloPendiente->revisado_por_user_id)->toBe($admin->id);

    // 6. Administrador Rechaza Artículo (con observaciones)
    $rechazarResponse = $this->actingAs($admin)->post(route('admin.articulos.rechazar', $articuloPendiente->id), [
        'motivo' => 'Corrección requerida en la sección de discusión.',
    ]);
    $rechazarResponse->assertRedirect();
    $articuloPendiente->refresh();
    expect($articuloPendiente->estado_revision)->toBe('rechazado');
    expect($articuloPendiente->art_estado)->toBeFalse();
    expect($articuloPendiente->revision_motivo)->toBe('Corrección requerida en la sección de discusión.');
});

test('autor puede crear articulo y editar el propio, pero no puede aprobar ni editar articulos ajenos ni eliminarlos en estado pendiente', function () {
    $autor1 = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'secret1',
        'two_factor_confirmed_at' => now(),
    ]);
    $autor1->assignRole('autor');

    $autor2 = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'secret2',
        'two_factor_confirmed_at' => now(),
    ]);
    $autor2->assignRole('autor');

    $tipo = TipoArticulo::firstOrCreate(
        ['tar_nombre' => 'Artículo Original'],
        ['tar_descripcion' => 'Original', 'tar_estado' => true]
    );

    // 1. Autor 1 crea su propio artículo
    $postResponse = $this->actingAs($autor1)->post(route('admin.articulos.store'), [
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Modelos Bayesianos Aplicados a Demografía',
        'art_slug' => 'modelos-bayesianos-demografia',
        'art_resumen' => 'Resumen del artículo por Autor 1.',
        'art_fecha_recepcion' => '2026-01-01', // Se sobreescribirá a la fecha de hoy
        'art_fecha_aceptado' => '2026-12-31', // No tiene permiso para fijar fecha aceptado
    ]);
    $postResponse->assertRedirect();

    $articulo1 = Articulo::where('art_slug', 'modelos-bayesianos-demografia')->firstOrFail();
    expect($articulo1->created_by_user_id)->toBe($autor1->id);
    expect($articulo1->estado_revision)->toBe('pendiente');
    expect($articulo1->art_fecha_aceptado)->toBeNull();

    // 2. Autor 1 puede editar su propio artículo
    $updateResponse = $this->actingAs($autor1)->put(route('admin.articulos.update', $articulo1->id), [
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Modelos Bayesianos Aplicados a Demografía - Edición Propia',
        'art_slug' => 'modelos-bayesianos-demografia',
        'art_resumen' => 'Resumen editado por el propio autor.',
        'art_fecha_recepcion' => now()->toDateString(),
    ]);
    $updateResponse->assertRedirect();
    $articulo1->refresh();
    expect($articulo1->art_titulo)->toBe('Modelos Bayesianos Aplicados a Demografía - Edición Propia');

    // 3. Autor 2 NO puede editar el artículo de Autor 1 (403 Forbidden)
    $forbiddenEdit = $this->actingAs($autor2)->put(route('admin.articulos.update', $articulo1->id), [
        'tipo_articulo_id' => $tipo->id,
        'art_titulo' => 'Intento de Hackeo por Autor 2',
        'art_slug' => 'modelos-bayesianos-demografia',
        'art_resumen' => 'No autorizado.',
        'art_fecha_recepcion' => now()->toDateString(),
    ]);
    $forbiddenEdit->assertStatus(403);

    // 4. Autor 1 NO puede aprobar su propio artículo ni ningún otro (403 Forbidden)
    $forbiddenAprobar = $this->actingAs($autor1)->post(route('admin.articulos.aprobar', $articulo1->id), [
        'motivo' => 'Auto-aprobación no permitida.',
    ]);
    $forbiddenAprobar->assertStatus(403);

    // 5. Autor 1 NO puede eliminar su artículo mientras esté en estado pendiente o aprobado (403 Forbidden)
    $forbiddenDelete = $this->actingAs($autor1)->delete(route('admin.articulos.destroy', $articulo1->id));
    $forbiddenDelete->assertStatus(403);
    $this->assertDatabaseHas('articulos', ['id' => $articulo1->id]);
});

/*
|--------------------------------------------------------------------------
| 2. FLUJO CRUD DE REVISTAS Y EDICIONES VARIANZA
|--------------------------------------------------------------------------
*/

test('administrador puede crear, editar y eliminar ediciones de Revista Varianza', function () {
    $admin = User::factory()->withTwoFactor()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    // 1. Crear Edición
    $createResponse = $this->actingAs($admin)->post(route('admin.ediciones-varianza.store'), [
        'ev_titulo' => 'Revista Varianza Número 30',
        'ev_numero' => '30',
        'ev_volumen' => 'Vol. 15',
        'ev_anio' => 2026,
        'ev_mes' => 'Enero - Junio',
        'ev_slug' => 'revista-varianza-numero-30',
        'ev_editorial' => 'Instituto de Estadística Teórica y Aplicada',
        'ev_estado' => true,
    ]);
    $createResponse->assertRedirect();
    $this->assertDatabaseHas('ediciones_varianzas', [
        'ev_slug' => 'revista-varianza-numero-30',
        'ev_numero' => '30',
    ]);

    $edicion = EdicionVarianza::where('ev_slug', 'revista-varianza-numero-30')->firstOrFail();

    // 2. Editar Edición
    $updateResponse = $this->actingAs($admin)->put(route('admin.ediciones-varianza.update', $edicion->id), [
        'ev_titulo' => 'Revista Varianza Número 30 - Especial IA y Estadística',
        'ev_numero' => '30',
        'ev_volumen' => 'Vol. 15 Edición Especial',
        'ev_anio' => 2026,
        'ev_mes' => 'Enero - Junio',
        'ev_slug' => 'revista-varianza-numero-30',
        'ev_editorial' => 'IETA - UMSA',
        'ev_estado' => true,
    ]);
    $updateResponse->assertRedirect();
    $edicion->refresh();
    expect($edicion->ev_titulo)->toBe('Revista Varianza Número 30 - Especial IA y Estadística');

    // 3. Eliminar Edición
    $deleteResponse = $this->actingAs($admin)->delete(route('admin.ediciones-varianza.destroy', $edicion->id));
    $deleteResponse->assertRedirect(route('admin.ediciones-varianza.index'));
    $this->assertDatabaseMissing('ediciones_varianzas', ['id' => $edicion->id]);
});

test('autor no tiene acceso para crear, editar o eliminar ediciones de Revista Varianza', function () {
    $autor = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'secret_autor',
        'two_factor_confirmed_at' => now(),
    ]);
    $autor->assignRole('autor');

    $edicion = EdicionVarianza::create([
        'ev_titulo' => 'Revista Varianza Edición Base',
        'ev_numero' => '99',
        'ev_anio' => 2026,
        'ev_slug' => 'revista-varianza-edicion-base',
        'ev_estado' => true,
    ]);

    // Intentar crear -> 403
    $this->actingAs($autor)->post(route('admin.ediciones-varianza.store'), [
        'ev_titulo' => 'Edición No Autorizada',
        'ev_numero' => '100',
        'ev_anio' => 2026,
    ])->assertStatus(403);

    // Intentar editar -> 403
    $this->actingAs($autor)->put(route('admin.ediciones-varianza.update', $edicion->id), [
        'ev_titulo' => 'Edición Modificada Sin Permiso',
        'ev_numero' => '99',
        'ev_anio' => 2026,
    ])->assertStatus(403);

    // Intentar eliminar -> 403
    $this->actingAs($autor)->delete(route('admin.ediciones-varianza.destroy', $edicion->id))->assertStatus(403);
    $this->assertDatabaseHas('ediciones_varianzas', ['id' => $edicion->id]);
});

/*
|--------------------------------------------------------------------------
| 3. FLUJO CRUD DE USUARIOS Y ROLES (RBAC)
|--------------------------------------------------------------------------
*/

test('administrador puede crear, editar, alternar estado y eliminar usuarios', function () {
    $admin = User::factory()->withTwoFactor()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    // 1. Crear Usuario con rol 'autor'
    $postResponse = $this->actingAs($admin)->post(route('admin.usuarios.store'), [
        'name' => 'Docente Investigador Test',
        'email' => 'docente_test@umsa.bo',
        'password' => 'PasswordSegura2026!',
        'role' => 'autor',
        'is_active' => true,
    ]);
    $postResponse->assertRedirect(route('admin.usuarios.index'));
    $this->assertDatabaseHas('users', ['email' => 'docente_test@umsa.bo', 'name' => 'Docente Investigador Test']);

    $nuevoUsuario = User::where('email', 'docente_test@umsa.bo')->firstOrFail();
    expect($nuevoUsuario->hasRole('autor'))->toBeTrue();

    // 2. Editar Usuario (nombre, email y rol a 'revisor')
    Role::firstOrCreate(['name' => 'revisor', 'guard_name' => 'web']);

    $putResponse = $this->actingAs($admin)->put(route('admin.usuarios.update', $nuevoUsuario->id), [
        'name' => 'Docente Investigador Actualizado',
        'email' => 'docente_actualizado@umsa.bo',
        'role' => 'revisor',
        'is_active' => true,
    ]);
    $putResponse->assertRedirect(route('admin.usuarios.index'));

    $nuevoUsuario->refresh();
    expect($nuevoUsuario->name)->toBe('Docente Investigador Actualizado');
    expect($nuevoUsuario->email)->toBe('docente_actualizado@umsa.bo');
    expect($nuevoUsuario->hasRole('revisor'))->toBeTrue();

    // 3. Alternar estado activo / inactivo
    $toggleResponse = $this->actingAs($admin)->patch(route('admin.usuarios.toggle-activo', $nuevoUsuario->id));
    $toggleResponse->assertRedirect();
    $nuevoUsuario->refresh();
    expect($nuevoUsuario->is_active)->toBeFalse();

    // 4. Eliminar Usuario
    $deleteResponse = $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $nuevoUsuario->id));
    $deleteResponse->assertRedirect(route('admin.usuarios.index'));
    $this->assertDatabaseMissing('users', ['id' => $nuevoUsuario->id]);

    // 5. El administrador en sesión no puede eliminar su propia cuenta
    $selfDelete = $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $admin->id));
    $selfDelete->assertRedirect(route('admin.usuarios.index'));
    $selfDelete->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('administrador puede crear, editar y eliminar roles personalizados sin afectar roles base del sistema', function () {
    $admin = User::factory()->withTwoFactor()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    // 1. Crear nuevo rol con permisos
    $perm1 = Permission::firstOrCreate(['name' => 'articulos.crear', 'guard_name' => 'web'], ['modulo' => 'Artículos']);
    $perm2 = Permission::firstOrCreate(['name' => 'articulos.ver-propios', 'guard_name' => 'web'], ['modulo' => 'Artículos']);

    $storeResponse = $this->actingAs($admin)->post(route('admin.roles.store'), [
        'name' => 'asistente-editorial',
        'description' => 'Asistente técnico para revisión de formatos',
        'permissions' => ['articulos.crear', 'articulos.ver-propios'],
    ]);
    $storeResponse->assertRedirect(route('admin.roles.index'));
    $this->assertDatabaseHas('roles', ['name' => 'asistente-editorial']);

    $nuevoRol = Role::findByName('asistente-editorial', 'web');
    expect($nuevoRol->hasPermissionTo('articulos.crear'))->toBeTrue();
    expect($nuevoRol->hasPermissionTo('articulos.ver-propios'))->toBeTrue();

    // 2. Editar el rol (modificar nombre y permisos)
    $perm3 = Permission::firstOrCreate(['name' => 'cursos.gestionar', 'guard_name' => 'web'], ['modulo' => 'Cursos']);

    $updateResponse = $this->actingAs($admin)->put(route('admin.roles.update', $nuevoRol->id), [
        'name' => 'asistente-editorial-senior',
        'description' => 'Asistente con apoyo a cursos',
        'permissions' => ['articulos.crear', 'cursos.gestionar'],
    ]);
    $updateResponse->assertRedirect(route('admin.roles.index'));

    $nuevoRol->refresh();
    expect($nuevoRol->name)->toBe('asistente-editorial-senior');
    expect($nuevoRol->hasPermissionTo('cursos.gestionar'))->toBeTrue();
    expect($nuevoRol->hasPermissionTo('articulos.ver-propios'))->toBeFalse();

    // 3. Eliminar el rol personalizado
    $deleteResponse = $this->actingAs($admin)->delete(route('admin.roles.destroy', $nuevoRol->id));
    $deleteResponse->assertRedirect(route('admin.roles.index'));
    $this->assertDatabaseMissing('roles', ['name' => 'asistente-editorial-senior']);

    // 4. Intentar eliminar el rol protegido 'administrador' debe ser rechazado
    $adminRole = Role::findByName('administrador', 'web');
    $protectedDelete = $this->actingAs($admin)->delete(route('admin.roles.destroy', $adminRole->id));
    $protectedDelete->assertRedirect(route('admin.roles.index'));
    $protectedDelete->assertSessionHas('error');
    $this->assertDatabaseHas('roles', ['name' => 'administrador']);
});

test('autor no puede gestionar usuarios ni roles', function () {
    $autor = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'secret_autor',
        'two_factor_confirmed_at' => now(),
    ]);
    $autor->assignRole('autor');

    $targetUser = User::factory()->create(['is_active' => true]);

    // Intentar acceder al listado de usuarios -> 403
    $this->actingAs($autor)->get(route('admin.usuarios.index'))->assertStatus(403);

    // Intentar crear usuario -> 403
    $this->actingAs($autor)->post(route('admin.usuarios.store'), [
        'name' => 'Infiltrado',
        'email' => 'infiltrado@umsa.bo',
        'password' => 'secret12345',
        'role' => 'administrador',
    ])->assertStatus(403);

    // Intentar eliminar usuario -> 403
    $this->actingAs($autor)->delete(route('admin.usuarios.destroy', $targetUser->id))->assertStatus(403);

    // Intentar acceder al listado de roles -> 403
    $this->actingAs($autor)->get(route('admin.roles.index'))->assertStatus(403);

    // Intentar crear rol -> 403
    $this->actingAs($autor)->post(route('admin.roles.store'), [
        'name' => 'super-hacker',
        'permissions' => [],
    ])->assertStatus(403);
});
