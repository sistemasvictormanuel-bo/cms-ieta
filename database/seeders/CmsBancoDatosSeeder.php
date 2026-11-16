<?php

namespace Database\Seeders;

use App\Models\CmsBancoDatos;
use Illuminate\Database\Seeder;

class CmsBancoDatosSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_banco_datos.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $bancos = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($bancos)) {
            return;
        }

        foreach ($bancos as $item) {
            CmsBancoDatos::updateOrCreate(
                ['id' => $item['id']],
                [
                    'cbd_titulo' => $item['cbd_titulo'],
                    'cbd_descripcion' => $item['cbd_descripcion'] ?? null,
                    'cbd_fuente' => $item['cbd_fuente'] ?? null,
                    'cbd_anio' => $item['cbd_anio'] ?? null,
                    'cbd_enlace_url' => $item['cbd_enlace_url'] ?? null,
                    'cbd_estado' => (bool) ($item['cbd_estado'] ?? true),
                    'cbd_es_destacado' => (bool) ($item['cbd_es_destacado'] ?? false),
                    'area_tematica_id' => $item['area_tematica_id'] ?? null,
                ]
            );
        }
    }
}
