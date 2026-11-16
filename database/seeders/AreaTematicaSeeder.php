<?php

namespace Database\Seeders;

use App\Models\AreaTematicas;
use Illuminate\Database\Seeder;

class AreaTematicaSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/area_tematicas.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $areas = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($areas)) {
            return;
        }

        foreach ($areas as $item) {
            AreaTematicas::updateOrCreate(
                ['id' => $item['id']],
                [
                    'at_nombre' => $item['at_nombre'],
                    'at_descripcion' => $item['at_descripcion'] ?? null,
                    'at_estado' => (bool) ($item['at_estado'] ?? true),
                ]
            );
        }
    }
}
