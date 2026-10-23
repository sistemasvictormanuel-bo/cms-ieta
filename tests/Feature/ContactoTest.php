<?php

use App\Models\CmsContacto;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
});

it('permite a un usuario autenticado ver la vista de edición de contacto institucional', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $response = $this->actingAs($user)->get(route('admin.contacto.edit'));

    $response->assertStatus(200);
});

it('permite actualizar el registro único institucional de contacto', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $payload = [
        'cc_telefono' => ['+591 2 2441234', '+591 71234567'],
        'cc_dia_inicio' => 'Lunes',
        'cc_dia_fin' => 'Viernes',
        'cc_hora_apertura' => '08:30',
        'cc_hora_cierre' => '18:30',
        'cc_direccion' => 'Av. Villazón N° 1995, Edificio Central IETA',
        'cc_email' => ['contacto@ieta.edu.bo', 'info@ieta.edu.bo'],
        'cc_contenido' => [
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => 'Indicaciones de atención presencial.',
                    ],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->put(route('admin.contacto.update'), $payload);

    $response->assertRedirect(route('admin.contacto.edit'));
    $response->assertSessionHas('success');

    $registro = CmsContacto::find(1);
    expect($registro)->not->toBeNull();
    expect($registro->cc_telefono)->toContain('+591 2 2441234');
    expect($registro->cc_email)->toContain('contacto@ieta.edu.bo');
    expect($registro->cc_direccion)->toBe('Av. Villazón N° 1995, Edificio Central IETA');
});

it('retorna la información institucional de contacto a través de la API pública', function () {
    CmsContacto::updateOrCreate(
        ['id' => 1],
        [
            'cc_telefono' => ['+591 2 2441234'],
            'cc_dia_inicio' => 'Lunes',
            'cc_dia_fin' => 'Viernes',
            'cc_hora_apertura' => '08:30:00',
            'cc_hora_cierre' => '18:30:00',
            'cc_direccion' => 'Av. Villazón N° 1995',
            'cc_email' => ['contacto@ieta.edu.bo'],
            'cc_contenido' => ['blocks' => []],
        ]
    );

    $response = $this->getJson('/api/contacto');

    $response->assertStatus(200);
    $response->assertJsonPath('telefonos', ['+591 2 2441234']);
    $response->assertJsonPath('direccion', 'Av. Villazón N° 1995');
    $response->assertJsonPath('horario_atencion.dia_inicio', 'Lunes');
});
