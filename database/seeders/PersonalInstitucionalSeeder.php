<?php

namespace Database\Seeders;

use App\Models\CargoInstitucional;
use App\Models\CmsComiteEditorial;
use App\Models\Persona;
use App\Models\Personal;
use Illuminate\Database\Seeder;

class PersonalInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/personal_institucional.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($data)) {
            return;
        }

        // 1. Cargos
        if (! empty($data['cargos'])) {
            foreach ($data['cargos'] as $c) {
                CargoInstitucional::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'ci_nombre' => $c['ci_nombre'],
                        'ci_descripcion' => $c['ci_descripcion'] ?? null,
                    ]
                );
            }
        }

        // 2. Personal
        if (! empty($data['personal'])) {
            foreach ($data['personal'] as $p) {
                $personaId = null;
                if (! empty($p['persona'])) {
                    $persona = Persona::updateOrCreate(
                        ['id' => $p['persona']['id']],
                        [
                            'p_nombre' => $p['persona']['p_nombre'],
                            'p_apellido_paterno' => $p['persona']['p_apellido_paterno'] ?? null,
                            'p_apellido_materno' => $p['persona']['p_apellido_materno'] ?? null,
                            'p_correo' => $p['persona']['p_correo'] ?? null,
                            'p_telefono' => $p['persona']['p_telefono'] ?? null,
                        ]
                    );
                    $personaId = $persona->id;
                }

                Personal::updateOrCreate(
                    ['id' => $p['id']],
                    [
                        'persona_id' => $personaId,
                        'cargo_institucional_id' => $p['cargo_id'] ?? null,
                        'per_biografia' => $p['per_biografia'] ?? null,
                        'per_estado' => (bool) ($p['per_estado'] ?? true),
                    ]
                );
            }
        }

        // 3. Comite Editorial
        if (! empty($data['comite'])) {
            foreach ($data['comite'] as $c) {
                CmsComiteEditorial::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'cce_nombre' => $c['cce_nombre'],
                        'cce_cargo' => $c['cce_cargo'] ?? null,
                        'cce_institucion' => $c['cce_institucion'] ?? null,
                        'cce_pais' => $c['cce_pais'] ?? null,
                        'cce_orden' => $c['cce_orden'] ?? 0,
                        'cce_estado' => (bool) ($c['cce_estado'] ?? true),
                    ]
                );
            }
        }
    }
}
