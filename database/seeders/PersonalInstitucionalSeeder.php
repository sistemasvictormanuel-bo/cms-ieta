<?php

namespace Database\Seeders;

use App\Models\CargoInstitucional;
use App\Models\CmsComiteEditorial;
use App\Models\Instituciones;
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
        $primerCargoId = null;
        if (! empty($data['cargos'])) {
            foreach ($data['cargos'] as $c) {
                $cargo = CargoInstitucional::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'ci_nombre' => $c['ci_nombre'],
                        'ci_descripcion' => $c['ci_descripcion'] ?? null,
                    ]
                );
                if (! $primerCargoId) {
                    $primerCargoId = $cargo->id;
                }
            }
        }

        if (! $primerCargoId) {
            $primerCargoId = CargoInstitucional::firstOrCreate(
                ['ci_nombre' => 'Investigador'],
                ['ci_descripcion' => 'Personal científico de investigación']
            )->id;
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
                            'p_celular' => $p['persona']['p_celular'] ?? $p['persona']['p_telefono'] ?? null,
                            'p_sitio_web' => $p['persona']['p_sitio_web'] ?? null,
                        ]
                    );
                    $personaId = $persona->id;
                }

                Personal::updateOrCreate(
                    ['id' => $p['id']],
                    [
                        'persona_id' => $personaId,
                        'cargo_id' => $p['cargo_id'] ?? $primerCargoId,
                        'pers_descripcion' => $p['per_biografia'] ?? null,
                        'pers_estado' => (bool) ($p['per_estado'] ?? true),
                    ]
                );
            }
        }

        // 3. Comite Editorial
        if (! empty($data['comite'])) {
            $institucionDefault = Instituciones::firstOrCreate(
                ['inst_nombre' => 'Universidad Mayor de San Andrés (UMSA)'],
                ['inst_pais' => 'Bolivia', 'inst_ciudad' => 'La Paz']
            )->id;

            foreach ($data['comite'] as $c) {
                $personaId = $c['persona_id'] ?? null;
                if (! $personaId || ! Persona::where('id', $personaId)->exists()) {
                    $personaId = Persona::firstOrCreate(
                        ['id' => $c['persona_id'] ?? 15],
                        [
                            'p_nombre' => 'Miembro',
                            'p_apellido_paterno' => 'Comité Editorial',
                            'p_correo' => 'comite_editorial@ieta.edu.bo',
                        ]
                    )->id;
                }

                $cargoId = $c['cargo_id'] ?? $primerCargoId;
                if (! CargoInstitucional::where('id', $cargoId)->exists()) {
                    $cargoId = $primerCargoId;
                }

                $institucionId = $c['institucion_id'] ?? $institucionDefault;
                if (! Instituciones::where('id', $institucionId)->exists()) {
                    $institucionId = $institucionDefault;
                }

                CmsComiteEditorial::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'persona_id' => $personaId,
                        'cargo_id' => $cargoId,
                        'institucion_id' => $institucionId,
                        'ce_tipo' => in_array($c['ce_tipo'] ?? '', ['Nacional', 'Internacional', 'Otro']) ? $c['ce_tipo'] : 'Nacional',
                    ]
                );
            }
        }
    }
}
