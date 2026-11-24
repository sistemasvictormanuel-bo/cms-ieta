<?php

namespace Database\Seeders;

use App\Models\CmsGaleria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        Model::unguard();

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

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_galerias', 'id'), COALESCE(MAX(id), 1)) FROM cms_galerias;");
        }
    }
}
