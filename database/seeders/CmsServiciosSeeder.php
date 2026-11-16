<?php

namespace Database\Seeders;

use App\Models\CmsServicios;
use Illuminate\Database\Seeder;

class CmsServiciosSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_servicios.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $servicios = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($servicios)) {
            return;
        }

        foreach ($servicios as $item) {
            CmsServicios::updateOrCreate(
                ['id' => $item['id']],
                [
                    'ser_titulo' => $item['ser_titulo'],
                    'ser_descripcion' => $item['ser_descripcion'] ?? null,
                    'ser_icono' => $item['ser_icono'] ?? null,
                    'ser_estado' => (bool) ($item['ser_estado'] ?? true),
                    'ser_orden' => $item['ser_orden'] ?? 0,
                ]
            );
        }
    }
}
