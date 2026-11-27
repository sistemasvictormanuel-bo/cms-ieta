<?php

namespace Database\Seeders;

use App\Models\CmsCursos;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsCursosSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_cursos.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $cursos = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($cursos)) {
            return;
        }

        Model::unguard();

        foreach ($cursos as $item) {
            $docenteId = ! empty($item['docente_id']) && Persona::withTrashed()->where('id', $item['docente_id'])->exists()
                ? $item['docente_id']
                : null;

            CmsCursos::updateOrCreate(
                ['id' => $item['id']],
                [
                    'ct_titulo' => $item['ct_titulo'],
                    'tipo_curso_id' => $item['tipo_curso_id'],
                    'modalidad_curso_id' => $item['modalidad_curso_id'],
                    'docente_id' => $docenteId,
                    'ct_fecha_inicio' => $item['ct_fecha_inicio'],
                    'ct_fecha_fin' => $item['ct_fecha_fin'],
                    'ct_hora_inicio' => $item['ct_hora_inicio'] ?? null,
                    'ct_hora_fin' => $item['ct_hora_fin'] ?? null,
                    'ct_duracion' => $item['ct_duracion'] ?? null,
                    'ct_descripcion' => $item['ct_descripcion'] ?? null,
                    'ct_contenido' => $item['ct_contenido'] ?? ['blocks' => []],
                    'ct_email_info' => $item['ct_email_info'] ?? null,
                    'ct_telefono_ref' => $item['ct_telefono_ref'] ?? null,
                    'ct_slug' => $item['ct_slug'],
                    'ct_activo' => (bool) ($item['ct_activo'] ?? true),
                    'ct_costo' => $item['ct_costo'] ?? 0,
                ]
            );
        }

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_cursos', 'id'), COALESCE(MAX(id), 1)) FROM cms_cursos;");
        }
    }
}
