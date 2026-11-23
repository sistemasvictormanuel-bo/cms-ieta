<?php

namespace Database\Seeders;

use App\Models\CmsContacto;
use Illuminate\Database\Seeder;

class CmsContactoSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/cms_contactos.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            $telefonos = is_string($item['cc_telefono']) ? json_decode($item['cc_telefono'], true) : $item['cc_telefono'];
            $emails = is_string($item['cc_email']) ? json_decode($item['cc_email'], true) : $item['cc_email'];
            $contenido = is_string($item['cc_contenido']) ? json_decode($item['cc_contenido'], true) : $item['cc_contenido'];

            CmsContacto::updateOrCreate(
                ['id' => $item['id'] ?? 1],
                [
                    'cc_telefono' => $telefonos ?? [],
                    'cc_email' => $emails ?? [],
                    'cc_dia_inicio' => $item['cc_dia_inicio'] ?? 'Lunes',
                    'cc_dia_fin' => $item['cc_dia_fin'] ?? 'Viernes',
                    'cc_hora_apertura' => $item['cc_hora_apertura'] ?? '08:00:00',
                    'cc_hora_cierre' => $item['cc_hora_cierre'] ?? '16:00:00',
                    'cc_direccion' => $item['cc_direccion'] ?? '',
                    'cc_contenido' => $contenido ?? ['blocks' => []],
                ]
            );
        }
    }
}

