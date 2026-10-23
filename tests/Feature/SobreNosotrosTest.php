<?php

use App\Models\CmsSobreNosotros;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
});

it('permite a un usuario autenticado ver la vista de edición de sobre nosotros', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $response = $this->actingAs($user)->get(route('admin.sobre-nosotros.edit'));

    $response->assertStatus(200);
});

it('permite actualizar el registro único de sobre nosotros', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $payload = [
        'sn_mision' => 'Misión de prueba del IETA',
        'sn_vision' => 'Visión de prueba del IETA',
        'sn_historia' => 'Historia de prueba del IETA',
        'sn_objetivos' => [
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => 'Objetivos estratégicos de prueba',
                    ],
                ],
            ],
        ],
        'sn_contenido' => [
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => 'Contenido enriquecido institucional',
                    ],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->put(route('admin.sobre-nosotros.update'), $payload);

    $response->assertRedirect(route('admin.sobre-nosotros.edit'));
    $response->assertSessionHas('success');

    $registro = CmsSobreNosotros::find(1);
    expect($registro)->not->toBeNull();
    expect($registro->sn_mision)->toBe('Misión de prueba del IETA');
    expect($registro->sn_vision)->toBe('Visión de prueba del IETA');
});

it('retorna la información institucional de sobre nosotros a través de la API pública', function () {
    CmsSobreNosotros::updateOrCreate(
        ['id' => 1],
        [
            'sn_mision' => 'Misión IETA para API',
            'sn_vision' => 'Visión IETA para API',
            'sn_historia' => 'Historia IETA para API',
            'sn_objetivos' => ['blocks' => []],
            'sn_contenido' => ['blocks' => []],
        ]
    );

    $response = $this->getJson('/api/sobre-nosotros');

    $response->assertStatus(200)
        ->assertJsonPath('mision', 'Misión IETA para API')
        ->assertJsonPath('vision', 'Visión IETA para API');
});
