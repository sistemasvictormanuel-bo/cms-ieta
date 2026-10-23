<?php

use App\Models\Articulo;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use PragmaRX\Google2FA\Google2FA;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->inactive()->create([
        'email' => 'inactive@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'inactive@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('active users can authenticate', function () {
    $user = User::factory()->create([
        'email' => 'active@example.com',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'email' => 'active@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('public registration is disabled', function () {
    $response = $this->get('/register');

    $response->assertStatus(404);
});

test('author without two factor authentication is redirected to setup', function () {
    $author = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);
    $author->assignRole('autor');

    $response = $this->actingAs($author)->get(route('admin.articulos.index'));

    $response->assertRedirect(route('two-factor.setup'));
});

test('author with two factor authentication can access articles module', function () {
    $author = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'encrypted_secret',
        'two_factor_confirmed_at' => now(),
    ]);
    $author->assignRole('autor');

    $response = $this->actingAs($author)->get(route('admin.articulos.index'));

    $response->assertStatus(200);
});

test('author can enable two factor authentication via Fortify endpoints', function () {
    $author = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);
    $author->assignRole('autor');

    // 1. Habilitar 2FA sin necesidad de confirmación previa de contraseña
    $enableResponse = $this->actingAs($author)->post('/user/two-factor-authentication');
    $enableResponse->assertSessionHasNoErrors();

    $author->refresh();
    expect($author->two_factor_secret)->not->toBeNull();
    expect($author->two_factor_confirmed_at)->toBeNull();

    // 2. Obtener código QR en SVG
    $qrResponse = $this->actingAs($author)->get('/user/two-factor-qr-code');
    $qrResponse->assertStatus(200);
    $qrResponse->assertJsonStructure(['svg', 'url']);

    // 3. Obtener clave secreta para configuración manual
    $secretResponse = $this->actingAs($author)->get('/user/two-factor-secret-key');
    $secretResponse->assertStatus(200);
    $secretResponse->assertJsonStructure(['secretKey']);

    // 4. Confirmar con código TOTP válido
    $tfaEngine = app(Google2FA::class);
    $validOtp = $tfaEngine->getCurrentOtp(decrypt($author->two_factor_secret));

    $confirmResponse = $this->actingAs($author)->post('/user/confirmed-two-factor-authentication', [
        'code' => $validOtp,
    ]);
    $confirmResponse->assertSessionHasNoErrors();

    $author->refresh();
    expect($author->hasEnabledTwoFactorAuthentication())->toBeTrue();
    expect($author->two_factor_confirmed_at)->not->toBeNull();

    // 5. El autor ya puede acceder: dashboard lo redirige a sus artículos y tiene acceso 200
    $dashboardResponse = $this->actingAs($author)->get(route('dashboard'));
    $dashboardResponse->assertRedirect(route('admin.articulos.index'));

    $articulosResponse = $this->actingAs($author)->get(route('admin.articulos.index'));
    $articulosResponse->assertStatus(200);
});

test('author cannot access administrator modules', function () {
    $author = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'encrypted_secret',
        'two_factor_confirmed_at' => now(),
    ]);
    $author->assignRole('autor');

    // Intenta acceder al CRUD de usuarios
    $response = $this->actingAs($author)->get(route('admin.usuarios.index'));
    $response->assertStatus(403);

    // Intenta acceder al módulo de personal
    $response = $this->actingAs($author)->get(route('admin.personal.index'));
    $response->assertStatus(403);
});

test('administrator can access all admin modules', function () {
    $admin = User::factory()->create([
        'is_active' => true,
    ]);
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($admin)->get(route('admin.articulos.index'));
    $response->assertStatus(200);
});

test('author only sees their own articles', function () {
    $author1 = User::factory()->create(['two_factor_secret' => 'sec', 'two_factor_confirmed_at' => now()]);
    $author1->assignRole('autor');

    $author2 = User::factory()->create(['two_factor_secret' => 'sec', 'two_factor_confirmed_at' => now()]);
    $author2->assignRole('autor');

    $articulo1 = Articulo::create([
        'art_titulo' => 'Artículo del Autor 1',
        'art_fecha_recepcion' => now()->toDateString(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
        'created_by_user_id' => $author1->id,
    ]);

    $articulo2 = Articulo::create([
        'art_titulo' => 'Artículo del Autor 2',
        'art_fecha_recepcion' => now()->toDateString(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
        'created_by_user_id' => $author2->id,
    ]);

    $response = $this->actingAs($author1)->get(route('admin.articulos.index'));
    $response->assertStatus(200);

    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Articulos/Index')
        ->has('articulos.data', 1)
        ->where('articulos.data.0.id', $articulo1->id)
    );
});

test('author cannot approve articles', function () {
    $author = User::factory()->create(['two_factor_secret' => 'sec', 'two_factor_confirmed_at' => now()]);
    $author->assignRole('autor');

    $articulo = Articulo::create([
        'art_titulo' => 'Artículo Pendiente',
        'art_fecha_recepcion' => now()->toDateString(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
        'created_by_user_id' => $author->id,
    ]);

    $response = $this->actingAs($author)->post(route('admin.articulos.aprobar', $articulo->id));

    $response->assertStatus(403);
});

test('administrator can approve articles and publish them', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $articulo = Articulo::create([
        'art_titulo' => 'Artículo Para Aprobar',
        'art_fecha_recepcion' => now()->toDateString(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.articulos.aprobar', $articulo->id));

    $response->assertRedirect();
    $articulo->refresh();

    expect($articulo->estado_revision)->toBe('aprobado');
    expect($articulo->art_estado)->toBeTrue();
    expect($articulo->revisado_por_user_id)->toBe($admin->id);
});

test('administrator can reject article with observations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $articulo = Articulo::create([
        'art_titulo' => 'Artículo Con Fallas',
        'art_fecha_recepcion' => now()->toDateString(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.articulos.rechazar', $articulo->id), [
        'motivo' => 'Falta metodología y referencias.',
    ]);

    $response->assertRedirect();
    $articulo->refresh();

    expect($articulo->estado_revision)->toBe('rechazado');
    expect($articulo->art_estado)->toBeFalse();
    expect($articulo->revision_motivo)->toBe('Falta metodología y referencias.');
    expect($articulo->revisado_por_user_id)->toBe($admin->id);
});

test('public api only returns approved articles', function () {
    $aprobado = Articulo::create([
        'art_titulo' => 'Artículo Aprobado Público',
        'art_fecha_recepcion' => now()->toDateString(),
        'estado_revision' => 'aprobado',
        'art_estado' => true,
    ]);

    $pendiente = Articulo::create([
        'art_titulo' => 'Artículo Pendiente Oculto',
        'art_fecha_recepcion' => now()->toDateString(),
        'estado_revision' => 'pendiente',
        'art_estado' => false,
    ]);

    $response = $this->getJson('/api/articulos');
    $response->assertStatus(200);

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($aprobado->id);
    expect($ids)->not->toContain($pendiente->id);

    // Consulta directa por ID a un artículo no aprobado
    $responseShow = $this->getJson("/api/articulos/{$pendiente->id}");
    $responseShow->assertStatus(404);
});
