<?php

namespace Database\Seeders;

use App\Models\InstruccionAutor;
use Illuminate\Database\Seeder;

class InstruccionesAutoresSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/instrucciones_autores.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            $contenido = is_string($item['ia_contenido']) ? json_decode($item['ia_contenido'], true) : $item['ia_contenido'];

            InstruccionAutor::updateOrCreate(
                ['id' => $item['id']],
                [
                    'ia_titulo_seccion' => $item['ia_titulo_seccion'],
                    'ia_contenido' => $contenido ?? ['blocks' => []],
                    'ia_orden' => (int) ($item['ia_orden'] ?? 1),
                    'ia_activo' => (bool) ($item['ia_activo'] ?? true),
                ]
            );
        }
    }
}

