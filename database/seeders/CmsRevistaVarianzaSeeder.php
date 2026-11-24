<?php

namespace Database\Seeders;

use App\Models\CmsRevistaVarianza;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsRevistaVarianzaSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_revista_varianza.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        Model::unguard();

        foreach ($items as $item) {
            $contenido = is_string($item['rv_contenido']) ? json_decode($item['rv_contenido'], true) : $item['rv_contenido'];
            $alcance = is_string($item['rv_alcance']) ? json_decode($item['rv_alcance'], true) : $item['rv_alcance'];
            $politicas = is_string($item['rv_politicas']) ? json_decode($item['rv_politicas'], true) : $item['rv_politicas'];

            CmsRevistaVarianza::updateOrCreate(
                ['id' => $item['id'] ?? 1],
                [
                    'rv_titulo' => $item['rv_titulo'] ?? 'Revista Varianza',
                    'rv_subtitulo' => $item['rv_subtitulo'] ?? null,
                    'rv_mision' => $item['rv_mision'] ?? null,
                    'rv_vision' => $item['rv_vision'] ?? null,
                    'rv_contenido' => $contenido ?? ['blocks' => []],
                    'rv_alcance' => $alcance ?? ['blocks' => []],
                    'rv_politicas' => $politicas ?? ['blocks' => []],
                    'rv_issn_impreso' => $item['rv_issn_impreso'] ?? '2076-6645',
                    'rv_issn_electronico' => $item['rv_issn_electronico'] ?? '2664-0899',
                    'rv_periodicidad' => $item['rv_periodicidad'] ?? 'Semestral',
                    'rv_correo_contacto' => $item['rv_correo_contacto'] ?? null,
                ]
            );
        }

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_revista_varianzas', 'id'), COALESCE(MAX(id), 1)) FROM cms_revista_varianzas;");
        }
    }
}
