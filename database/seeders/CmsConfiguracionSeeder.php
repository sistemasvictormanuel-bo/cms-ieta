<?php

namespace Database\Seeders;

use App\Models\CmsConfiguracion;
use Illuminate\Database\Seeder;

class CmsConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_configuracion.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $config = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($config)) {
            return;
        }

        CmsConfiguracion::updateOrCreate(
            ['id' => $config['id'] ?? 1],
            [
                'cfg_nombre_institucion' => $config['cfg_nombre_institucion'] ?? 'Instituto de Estadística Teórica y Aplicada',
                'cfg_siglas' => $config['cfg_siglas'] ?? 'IETA',
                'cfg_slogan' => $config['cfg_slogan'] ?? 'Investigación, rigor científico y análisis estadístico',
                'cfg_descripcion_corta' => $config['cfg_descripcion_corta'] ?? null,
                'cfg_email_principal' => $config['cfg_email_principal'] ?? 'contacto@ieta.umsa.bo',
                'cfg_telefono_principal' => $config['cfg_telefono_principal'] ?? null,
                'cfg_direccion_principal' => $config['cfg_direccion_principal'] ?? null,
                'cfg_redes_sociales' => $config['cfg_redes_sociales'] ?? [],
                'cfg_copyright' => $config['cfg_copyright'] ?? ('© '.date('Y').' Instituto de Estadística Teórica y Aplicada (IETA). UMSA.'),
            ]
        );
    }
}
