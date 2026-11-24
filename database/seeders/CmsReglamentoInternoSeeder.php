<?php

namespace Database\Seeders;

use App\Models\CmsReglamentoInterno;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsReglamentoInternoSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_reglamento_interno.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        Model::unguard();

        foreach ($items as $item) {
            $contenido = is_string($item['reg_contenido']) ? json_decode($item['reg_contenido'], true) : $item['reg_contenido'];

            CmsReglamentoInterno::updateOrCreate(
                ['id' => $item['id'] ?? 1],
                [
                    'reg_titulo' => $item['reg_titulo'] ?? 'Reglamento Interno Institucional',
                    'reg_descripcion' => $item['reg_descripcion'] ?? 'Normativa y disposiciones internas vigentes del Instituto de Estadística Teórica y Aplicada (IETA).',
                    'reg_contenido' => $contenido ?? ['blocks' => []],
                ]
            );
        }

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_reglamento_internos', 'id'), COALESCE(MAX(id), 1)) FROM cms_reglamento_internos;");
        }
    }
}
