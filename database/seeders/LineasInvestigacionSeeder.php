<?php

namespace Database\Seeders;

use App\Models\CmsLineasInvestigacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LineasInvestigacionSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/lineas_investigacion.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $lineas = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($lineas)) {
            return;
        }

        Model::unguard();

        foreach ($lineas as $item) {
            $titulo = $item['li_titulo'] ?? $item['cli_nombre'] ?? 'Línea de Investigación';
            $slug = $item['li_slug'] ?? Str::slug($titulo);

            CmsLineasInvestigacion::updateOrCreate(
                ['id' => $item['id']],
                [
                    'li_titulo' => $titulo,
                    'li_descripcion' => $item['li_descripcion'] ?? $item['cli_descripcion'] ?? 'Descripción de la línea de investigación.',
                    'li_contenido' => $item['li_contenido'] ?? ['time' => (string) time(), 'blocks' => [], 'version' => '2.31.7'],
                    'li_slug' => $slug,
                ]
            );
        }

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_lineas_investigaciones', 'id'), COALESCE(MAX(id), 1)) FROM cms_lineas_investigaciones;");
        }
    }
}
