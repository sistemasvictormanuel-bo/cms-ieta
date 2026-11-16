<?php

namespace Database\Seeders;

use App\Models\MemoriasInvestigacion;
use Illuminate\Database\Seeder;

class MemoriasInvestigacionSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/memorias_investigacion.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $memorias = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($memorias)) {
            return;
        }

        foreach ($memorias as $item) {
            MemoriasInvestigacion::updateOrCreate(
                ['id' => $item['id']],
                [
                    'autor_id' => $item['autor_id'] ?? null,
                    'mdi_titulo' => $item['mdi_titulo'],
                    'mdi_descripcion' => $item['mdi_descripcion'] ?? null,
                    'mdi_etiqueta' => $item['mdi_etiqueta'] ?? 'Proyecto de Investigación',
                    'mdi_enlace_url' => $item['mdi_enlace_url'] ?? null,
                    'mdi_anio' => $item['mdi_anio'] ?? 2024,
                ]
            );
        }
    }
}
