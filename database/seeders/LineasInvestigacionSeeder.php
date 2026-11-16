<?php

namespace Database\Seeders;

use App\Models\CmsLineasInvestigacion;
use Illuminate\Database\Seeder;

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

        foreach ($lineas as $item) {
            CmsLineasInvestigacion::updateOrCreate(
                ['id' => $item['id']],
                [
                    'cli_nombre' => $item['cli_nombre'],
                    'cli_descripcion' => $item['cli_descripcion'] ?? null,
                    'cli_icono' => $item['cli_icono'] ?? null,
                    'cli_estado' => (bool) ($item['cli_estado'] ?? true),
                    'cli_orden' => $item['cli_orden'] ?? 0,
                ]
            );
        }
    }
}
