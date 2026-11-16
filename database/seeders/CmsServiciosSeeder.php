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
            $titulo = $item['sv_titulo'] ?? $item['ser_titulo'] ?? 'Servicio IETA';

            CmsServicios::updateOrCreate(
                ['id' => $item['id']],
                [
                    'sv_titulo' => $titulo,
                    'sv_descripcion' => $item['sv_descripcion'] ?? $item['ser_descripcion'] ?? 'Descripción del servicio.',
                    'sv_contenido' => $item['sv_contenido'] ?? ['time' => (string) time(), 'blocks' => [], 'version' => '2.31.7'],
                ]
            );
        }
    }
}
