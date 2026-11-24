<?php

namespace Database\Seeders;

use App\Models\CargoInstitucional;
use App\Models\CmsComiteEditorial;
use App\Models\Instituciones;
use App\Models\Persona;
use App\Models\Personal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        Model::unguard();

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
            $primerCargo = CargoInstitucional::firstOrCreate(
                ['ci_nombre' => 'Investigador'],
                ['ci_descripcion' => 'Personal científico de investigación']
            );
            $primerCargoId = $primerCargo->id;
        }

        // 2. Personal
        if (! empty($data['personal'])) {
            foreach ($data['personal'] as $p) {
                $personaId = null;
                if (! empty($p['persona'])) {
                    $persona = Persona::withTrashed()->updateOrCreate(
                        ['id' => $p['persona']['id']],
                        [
                            'p_nombre' => $p['persona']['p_nombre'],
                            'p_apellido_paterno' => $p['persona']['p_apellido_paterno'] ?? null,
                            'p_apellido_materno' => $p['persona']['p_apellido_materno'] ?? null,
                            'p_correo' => $p['persona']['p_correo'] ?? null,
                            'p_celular' => $p['persona']['p_celular'] ?? $p['persona']['p_telefono'] ?? null,
                            'p_sitio_web' => $p['persona']['p_sitio_web'] ?? null,
                            'deleted_at' => null,
                        ]
                    );
                    $personaId = $persona->id;
                }

                Personal::withTrashed()->updateOrCreate(
                    ['id' => $p['id']],
                    [
                        'persona_id' => $personaId,
                        'cargo_id' => $p['cargo_id'] ?? $primerCargoId,
                        'pers_descripcion' => $p['per_biografia'] ?? null,
                        'pers_estado' => (bool) ($p['per_estado'] ?? true),
                        'deleted_at' => null,
                    ]
                );
            }
        }

        // 3. Comité Editorial
        if (! empty($data['comite'])) {
            $institucionDefault = Instituciones::find(1)?->id
                ?? Instituciones::firstOrCreate(
                    ['inst_nombre' => 'Universidad Mayor de San Andrés (UMSA)'],
                    ['inst_pais' => 'Bolivia', 'inst_ciudad' => 'La Paz']
                )->id;

            foreach ($data['comite'] as $c) {
                $personaId = $c['persona_id'] ?? null;
                if (! $personaId || ! Persona::withTrashed()->where('id', $personaId)->exists()) {
                    $comitePersonaId = $c['persona_id'] ?? 15;
                    $persona = Persona::withTrashed()->updateOrCreate(
                        ['id' => $comitePersonaId],
                        [
                            'p_nombre' => 'Miembro',
                            'p_apellido_paterno' => 'Comité Editorial',
                            'p_correo' => 'comite_editorial@ieta.edu.bo',
                            'deleted_at' => null,
                        ]
                    );
                    $personaId = $persona->id;
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

        Model::reguard();

        // 4. Sincronizar secuencias de PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('personal', 'id'), COALESCE(MAX(id), 1)) FROM personal;");
            DB::statement("SELECT setval(pg_get_serial_sequence('personas', 'id'), COALESCE(MAX(id), 1)) FROM personas;");
            DB::statement("SELECT setval(pg_get_serial_sequence('cargo_institucionales', 'id'), COALESCE(MAX(id), 1)) FROM cargo_institucionales;");
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_comite_editoriales', 'id'), COALESCE(MAX(id), 1)) FROM cms_comite_editoriales;");
        }
    }
}
