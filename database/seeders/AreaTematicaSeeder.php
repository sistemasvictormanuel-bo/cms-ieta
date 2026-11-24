<?php

namespace Database\Seeders;

use App\Models\AreaTematicas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        Model::unguard();

        foreach ($areas as $item) {
            AreaTematicas::updateOrCreate(
                ['id' => $item['id']],
                [
                    'at_nombre' => $item['at_nombre'],
                    'at_descripcion' => $item['at_descripcion'] ?? null,
                ]
            );
        }

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('area_tematicas', 'id'), COALESCE(MAX(id), 1)) FROM area_tematicas;");
        }
    }
}
