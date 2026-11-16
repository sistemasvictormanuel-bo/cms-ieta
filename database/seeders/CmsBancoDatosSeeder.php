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
            $titulo = $item['bdd_titulo'] ?? $item['cbd_titulo'] ?? 'Banco de Datos';

            CmsBancoDatos::updateOrCreate(
                ['id' => $item['id']],
                [
                    'bdd_tipo_dato' => $item['bdd_tipo_dato'] ?? 'Censos',
                    'bdd_anio' => $item['bdd_anio'] ?? $item['cbd_anio'] ?? date('Y'),
                    'bdd_codigo' => $item['bdd_codigo'] ?? ('BDD-'.$item['id']),
                    'bdd_titulo' => $titulo,
                    'bdd_descripcion' => $item['bdd_descripcion'] ?? $item['cbd_descripcion'] ?? null,
                    'bdd_locacion' => $item['bdd_locacion'] ?? 'Bolivia',
                    'bdd_url' => $item['bdd_url'] ?? $item['cbd_enlace_url'] ?? null,
                ]
            );
        }
    }
}
