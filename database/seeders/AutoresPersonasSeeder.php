<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Persona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AutoresPersonasSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/autores_personas.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $autores = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($autores)) {
            return;
        }

        foreach ($autores as $item) {
            $personaId = null;

            if (! empty($item['persona'])) {
                $pData = $item['persona'];
                $persona = Persona::updateOrCreate(
                    ['id' => $pData['id']],
                    [
                        'p_nombre' => $pData['p_nombre'],
                        'p_apellido_paterno' => $pData['p_apellido_paterno'] ?? null,
                        'p_apellido_materno' => $pData['p_apellido_materno'] ?? null,
                        'p_correo' => $pData['p_correo'] ?? null,
                        'p_telefono' => $pData['p_telefono'] ?? null,
                        'p_ci' => $pData['p_ci'] ?? null,
                    ]
                );
                $personaId = $persona->id;
            }

            $slug = $item['at_slug'] ?? Str::slug(($item['persona']['p_nombre'] ?? 'autor').' '.($item['persona']['p_apellido_paterno'] ?? $item['id']));

            $autor = Autor::updateOrCreate(
                ['id' => $item['id']],
                [
                    'persona_id' => $personaId,
                    'tipo_autor_id' => $item['tipo_autor_id'] ?? 1,
                    'grado_academico_id' => $item['grado_academico_id'] ?? null,
                    'at_slug' => $slug,
                    'at_biografia' => $item['at_biografia'] ?? null,
                    'at_orcid' => $item['at_orcid'] ?? null,
                    'at_estado' => (bool) ($item['at_estado'] ?? true),
                ]
            );

            if (! empty($item['especialidades_ids']) && is_array($item['especialidades_ids'])) {
                $autor->especialidades()->syncWithoutDetaching($item['especialidades_ids']);
            }
        }
    }
}
