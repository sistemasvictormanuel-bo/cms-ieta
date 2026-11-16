<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Especialidades;
use App\Models\GradoAcademico;
use App\Models\Persona;
use App\Models\TipoAutor;
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
                        'p_celular' => $pData['p_celular'] ?? $pData['p_telefono'] ?? null,
                        'p_sitio_web' => $pData['p_sitio_web'] ?? null,
                    ]
                );
                $personaId = $persona->id;
            }

            $slug = $item['at_slug'] ?? Str::slug(($item['persona']['p_nombre'] ?? 'autor').' '.($item['persona']['p_apellido_paterno'] ?? $item['id']));

            $tipoAutorId = ($item['tipo_autor_id'] ?? null) && TipoAutor::where('id', $item['tipo_autor_id'])->exists()
                ? $item['tipo_autor_id']
                : 1;

            $gradoId = ($item['grado_academico_id'] ?? null) && GradoAcademico::where('id', $item['grado_academico_id'])->exists()
                ? $item['grado_academico_id']
                : (GradoAcademico::first()?->id ?? 1);

            $autor = Autor::updateOrCreate(
                ['id' => $item['id']],
                [
                    'persona_id' => $personaId,
                    'tipo_autor_id' => $tipoAutorId,
                    'grado_academico_id' => $gradoId,
                    'at_slug' => $slug,
                    'at_biografia' => $item['at_biografia'] ?? null,
                    'at_intereses_cientificos' => $item['at_intereses_cientificos'] ?? null,
                    'at_estado' => (bool) ($item['at_estado'] ?? true),
                ]
            );

            if (! empty($item['especialidades_ids']) && is_array($item['especialidades_ids'])) {
                $validEspecialidades = Especialidades::whereIn('id', $item['especialidades_ids'])->pluck('id')->toArray();
                if (! empty($validEspecialidades)) {
                    $autor->especialidades()->syncWithoutDetaching($validEspecialidades);
                }
            }
        }
    }
}
