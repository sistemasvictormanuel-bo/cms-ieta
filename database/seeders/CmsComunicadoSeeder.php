<?php

namespace Database\Seeders;

use App\Models\CmsComunicado;
use Illuminate\Database\Seeder;

class CmsComunicadoSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_comunicados.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            CmsComunicado::updateOrCreate(
                ['id' => $item['id']],
                [
                    'com_titulo' => $item['com_titulo'],
                    'com_descripcion' => $item['com_descripcion'] ?? null,
                    'com_enlace_url' => $item['com_enlace_url'] ?? null,
                    'com_texto_boton' => $item['com_texto_boton'] ?? 'Más información',
                    'com_tipo' => $item['com_tipo'] ?? 'general',
                    'com_fecha_inicio' => $item['com_fecha_inicio'] ?? now()->toDateString(),
                    'com_fecha_fin' => $item['com_fecha_fin'] ?? null,
                    'com_activo' => (bool) ($item['com_activo'] ?? true),
                    'com_vistas' => (int) ($item['com_vistas'] ?? 0),
                ]
            );
        }
    }
}

