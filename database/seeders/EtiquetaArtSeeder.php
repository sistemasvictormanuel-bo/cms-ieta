<?php

namespace Database\Seeders;

use App\Models\EtiquetaArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EtiquetaArtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $etiquetas = [
            'Inteligencia Artificial',
            'Econometría',
            'Series de Tiempo',
            'Sistemas Complejos',
            'Desarrollo Sostenible',
            'Biotecnología',
            'Políticas Públicas',
            'Educación Superior',
            'Gestión de Datos',
            'Modelos Matemáticos',
        ];

        foreach ($etiquetas as $nombre) {
            EtiquetaArt::firstOrCreate(
                ['ea_slug' => Str::slug($nombre)],
                ['ea_nombre' => $nombre]
            );
        }
    }
}
