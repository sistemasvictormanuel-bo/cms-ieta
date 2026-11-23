<?php

namespace Database\Seeders;

use App\Models\CmsGaleria;
use Illuminate\Database\Seeder;

class CmsGaleriaSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_galerias.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            CmsGaleria::updateOrCreate(
                ['id' => $item['id']],
                [
                    'gal_titulo' => $item['gal_titulo'],
                    'gal_descripcion' => $item['gal_descripcion'] ?? null,
                    'gal_categoria' => $item['gal_categoria'] ?? 'Infraestructura y Ambientes',
                    'gal_fecha' => $item['gal_fecha'] ?? null,
                    'gal_lugar' => $item['gal_lugar'] ?? null,
                    'gal_destacado' => (bool) ($item['gal_destacado'] ?? false),
                    'gal_activo' => (bool) ($item['gal_activo'] ?? true),
                    'gal_orden' => (int) ($item['gal_orden'] ?? 0),
                ]
            );
        }
    }
}

