<?php

use App\Models\CargoInstitucional;
use App\Models\CmsGaleria;
use App\Models\Especialidades;
use App\Models\GradoAcademico;
use App\Models\Instituciones;
use App\Models\TipoAutor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'autor', 'guard_name' => 'web']);
});

it('permite registrar un autor creando cuenta de usuario con rol autor de forma directa', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $tipo = TipoAutor::firstOrCreate(['ta_nombre' => 'Titular'], ['ta_descripcion' => 'Autor Titular']);
    $grado = GradoAcademico::firstOrCreate(['ga_nombre' => 'Doctor'], ['ga_abreviatura' => 'Dr.', 'ga_estado' => true]);
    $especialidad = Especialidades::firstOrCreate(['ep_nombre' => 'Estadística Bayesiana'], ['ep_descripcion' => 'Bayes', 'ep_estado' => true]);

    $response = $this->actingAs($admin)->post(route('admin.autores.store'), [
        'p_nombre' => 'Carlos',
        'p_apellido_paterno' => 'Mendoza',
        'p_apellido_materno' => 'Flores',
        'p_correo' => 'cmendoza_autor@ieta.edu.bo',
        'p_celular' => '70011223',
        'crear_usuario' => true,
        'password' => 'Password123!',
        'rol' => 'autor',
        'user_is_active' => true,
        'tipo_autor_id' => $tipo->id,
        'especialidades' => [$especialidad->id],
        'grado_academico_id' => $grado->id,
        'at_estado' => true,
    ]);

    $response->assertRedirect(route('admin.autores.index'));

    $user = User::where('email', 'cmendoza_autor@ieta.edu.bo')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('autor'))->toBeTrue();
    expect($user->is_active)->toBeTrue();
    expect($user->persona)->not->toBeNull();
    expect($user->persona->autor)->not->toBeNull();
});

it('permite registrar un autor sin crear cuenta de usuario (user_id nulo)', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $tipo = TipoAutor::firstOrCreate(['ta_nombre' => 'Invitado'], ['ta_descripcion' => 'Autor Invitado']);
    $grado = GradoAcademico::firstOrCreate(['ga_nombre' => 'Magister'], ['ga_abreviatura' => 'MSc.', 'ga_estado' => true]);
    $especialidad = Especialidades::firstOrCreate(['ep_nombre' => 'Muestreo'], ['ep_descripcion' => 'Muestreo', 'ep_estado' => true]);

    $response = $this->actingAs($admin)->post(route('admin.autores.store'), [
        'p_nombre' => 'Laura',
        'p_apellido_paterno' => 'Torres',
        'p_apellido_materno' => 'Vega',
        'p_correo' => 'ltorres_sin_cuenta@externo.edu.bo',
        'crear_usuario' => false,
        'tipo_autor_id' => $tipo->id,
        'especialidades' => [$especialidad->id],
        'grado_academico_id' => $grado->id,
        'at_estado' => true,
    ]);

    $response->assertRedirect(route('admin.autores.index'));

    $user = User::where('email', 'ltorres_sin_cuenta@externo.edu.bo')->first();
    expect($user)->toBeNull();

    $persona = \App\Models\Persona::where('p_correo', 'ltorres_sin_cuenta@externo.edu.bo')->first();
    expect($persona)->not->toBeNull();
    expect($persona->user_id)->toBeNull();
    expect($persona->autor)->not->toBeNull();
});

it('permite registrar personal con alta directa de usuario administrador', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $cargo = CargoInstitucional::firstOrCreate(['ci_nombre' => 'Coordinador'], ['ci_descripcion' => 'Coord']);

    $response = $this->actingAs($admin)->post(route('admin.personal.store'), [
        'p_nombre' => 'Fernando',
        'p_apellido_paterno' => 'Quispe',
        'p_correo' => 'fquispe_personal@ieta.edu.bo',
        'crear_usuario' => true,
        'password' => 'Password123!',
        'rol' => 'administrador',
        'user_is_active' => true,
        'cargo_id' => $cargo->id,
        'pers_estado' => true,
    ]);

    $response->assertRedirect(route('admin.personal.index'));

    $user = User::where('email', 'fquispe_personal@ieta.edu.bo')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('administrador'))->toBeTrue();
    expect($user->persona)->not->toBeNull();
    expect($user->persona->personal)->not->toBeNull();
});

it('permite guardar fotografia en modulo galeria recibiendo imagen', function () {
    Storage::fake('public');
    Storage::fake('media');

    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $file = UploadedFile::fake()->image('evento.jpg', 600, 400);

    $response = $this->actingAs($admin)->post(route('admin.galeria.store'), [
        'gal_titulo' => 'Fotografía Congreso 2026',
        'gal_categoria' => 'Eventos y Actividades',
        'gal_fecha' => '2026-10-13',
        'imagen' => $file,
    ]);

    $response->assertRedirect(route('admin.galeria.index'));

    $galeria = CmsGaleria::where('gal_titulo', 'Fotografía Congreso 2026')->first();
    expect($galeria)->not->toBeNull();
    expect($galeria->getFirstMedia('gal_imagen'))->not->toBeNull();
});
