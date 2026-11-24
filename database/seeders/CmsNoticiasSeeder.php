<?php

namespace Database\Seeders;

use App\Models\CmsNoticias;
use Illuminate\Database\Seeder;

class CmsNoticiasSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_noticias.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $noticias = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($noticias)) {
            return;
        }

        foreach ($noticias as $item) {
            CmsNoticias::updateOrCreate(
                ['id' => $item['id']],
                [
                    'persona_id' => $item['persona_id'] ?? null,
                    'not_titulo' => $item['not_titulo'],
                    'not_descripcion' => $item['not_descripcion'] ?? null,
                    'not_fecha' => $item['not_fecha'] ?? null,
                    'not_destacado' => (bool) ($item['not_destacado'] ?? false),
                    'not_publicado' => (bool) ($item['not_publicado'] ?? true),
                    'not_contenido' => $item['not_contenido'] ?? null,
                    'not_url' => $item['not_url'] ?? null,
                ]
            );
        }
    }
}

