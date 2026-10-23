<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('administrator can view roles index page', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->get(route('admin.roles.index'));

    $response->assertStatus(200);
});

test('administrator can create a new custom role with assigned permissions', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->post(route('admin.roles.store'), [
        'name' => 'coordinador',
        'description' => 'Coordinador general de investigación',
        'permissions' => [
            'articulos.crear',
            'articulos.ver-propios',
            'cursos.gestionar',
        ],
    ]);

    $response->assertRedirect(route('admin.roles.index'));
    $this->assertDatabaseHas('roles', ['name' => 'coordinador']);

    $role = Role::findByName('coordinador', 'web');
    expect($role->hasPermissionTo('cursos.gestionar'))->toBeTrue();
    expect($role->hasPermissionTo('articulos.crear'))->toBeTrue();
});

test('protected roles like administrador cannot be deleted', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $adminRole = Role::findByName('administrador', 'web');

    $response = $this->actingAs($admin)->delete(route('admin.roles.destroy', $adminRole->id));

    $response->assertRedirect(route('admin.roles.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('roles', ['name' => 'administrador']);
});

test('administrator can view permissions index page', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->get(route('admin.permisos.index'));

    $response->assertStatus(200);
});

test('administrator can create a permission for a new future module', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $response = $this->actingAs($admin)->post(route('admin.permisos.store'), [
        'name' => 'biblioteca.descargar-recursos',
        'modulo' => 'Biblioteca Digital',
        'description' => 'Permite descargar recursos exclusivos de la biblioteca',
        'roles' => ['administrador'],
    ]);

    $response->assertRedirect(route('admin.permisos.index'));
    $this->assertDatabaseHas('permissions', [
        'name' => 'biblioteca.descargar-recursos',
        'modulo' => 'Biblioteca Digital',
    ]);

    $adminRole = Role::findByName('administrador', 'web');
    expect($adminRole->hasPermissionTo('biblioteca.descargar-recursos'))->toBeTrue();
});

test('protected system permissions cannot be deleted', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $perm = Permission::findByName('usuarios.gestionar', 'web');

    $response = $this->actingAs($admin)->delete(route('admin.permisos.destroy', $perm->id));

    $response->assertRedirect(route('admin.permisos.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('permissions', ['name' => 'usuarios.gestionar']);
});

test('custom permission can be deleted', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');

    $perm = Permission::create([
        'name' => 'test.custom-perm',
        'modulo' => 'Testing',
        'guard_name' => 'web',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.permisos.destroy', $perm->id));

    $response->assertRedirect(route('admin.permisos.index'));
    $this->assertDatabaseMissing('permissions', ['name' => 'test.custom-perm']);
});

test('non-admin user without permission cannot access roles management', function () {
    $author = User::factory()->create([
        'is_active' => true,
        'two_factor_secret' => 'encrypted_secret',
        'two_factor_confirmed_at' => now(),
    ]);
    $author->assignRole('autor');

    $response = $this->actingAs($author)->get(route('admin.roles.index'));

    $response->assertStatus(403);
});
