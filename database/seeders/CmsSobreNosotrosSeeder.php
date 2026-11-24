<?php

namespace Database\Seeders;

use App\Models\CmsSobreNosotros;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsSobreNosotrosSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_sobre_nosotros.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        Model::unguard();

        foreach ($items as $item) {
            $contenido = is_string($item['sn_contenido']) ? json_decode($item['sn_contenido'], true) : $item['sn_contenido'];
            $objetivos = is_string($item['sn_objetivos']) ? json_decode($item['sn_objetivos'], true) : $item['sn_objetivos'];

            CmsSobreNosotros::updateOrCreate(
                ['id' => $item['id'] ?? 1],
                [
                    'sn_mision' => $item['sn_mision'],
                    'sn_vision' => $item['sn_vision'],
                    'sn_historia' => $item['sn_historia'],
                    'sn_objetivos' => $objetivos ?? ['blocks' => []],
                    'sn_contenido' => $contenido ?? ['blocks' => []],
                ]
            );
        }

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_sobre_nosotros', 'id'), COALESCE(MAX(id), 1)) FROM cms_sobre_nosotros;");
        }
    }
}
