<?php

namespace Database\Seeders;

use App\Models\CmsRevistasVarias;
use Illuminate\Database\Seeder;

class CmsRevistasVariasSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_revistas_varias.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            CmsRevistasVarias::updateOrCreate(
                ['id' => $item['id']],
                [
                    'rev_titulo' => $item['rev_titulo'],
                    'rev_descripcion' => $item['rev_descripcion'] ?? null,
                    'rev_url_enlace' => $item['rev_url_enlace'],
                    'rev_entidad' => $item['rev_entidad'] ?? null,
                    'rev_issn' => $item['rev_issn'] ?? null,
                    'rev_activo' => (bool) ($item['rev_activo'] ?? true),
                    'rev_orden' => (int) ($item['rev_orden'] ?? 1),
                ]
            );
        }
    }
}

