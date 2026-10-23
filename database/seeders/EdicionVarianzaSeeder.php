<?php

namespace Database\Seeders;

use App\Models\EdicionVarianza;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EdicionVarianzaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ediciones = [
            [
                'ev_titulo' => 'Revista Varianza - N° 25 (2024)',
                'ev_numero' => '25',
                'ev_volumen' => '15',
                'ev_anio' => 2024,
                'ev_mes' => 'Julio - Diciembre',
                'ev_slug' => Str::slug('Revista Varianza - N° 25 (2024)'),
                'ev_issn_ver_impresa' => '2076-6645',
                'ev_issn_ver_electronica' => '2664-0899',
                'ev_num_art_originales' => 8,
                'ev_notas_cientificas' => 2,
                'ev_num_paginas' => 110,
                'ev_doi' => '10.53287/varianza.2024.25',
                'ev_editorial' => 'Presentamos el número 25 de la Revista Varianza con destacadas investigaciones en estadística multivariada y modelos estocásticos.',
                'ev_fecha_publicacion' => '2024-12-15',
                'ev_estado' => true,
            ],
            [
                'ev_titulo' => 'Revista Varianza - N° 24 (2024)',
                'ev_numero' => '24',
                'ev_volumen' => '15',
                'ev_anio' => 2024,
                'ev_mes' => 'Enero - Junio',
                'ev_slug' => Str::slug('Revista Varianza - N° 24 (2024)'),
                'ev_issn_ver_impresa' => '2076-6645',
                'ev_issn_ver_electronica' => '2664-0899',
                'ev_num_art_originales' => 6,
                'ev_notas_cientificas' => 3,
                'ev_num_paginas' => 95,
                'ev_doi' => '10.53287/varianza.2024.24',
                'ev_editorial' => 'Edición dedicada a series temporales aplicadas a la economía y salud pública.',
                'ev_fecha_publicacion' => '2024-06-30',
                'ev_estado' => true,
            ],
        ];

        foreach ($ediciones as $edicion) {
            EdicionVarianza::firstOrCreate(
                ['ev_slug' => $edicion['ev_slug']],
                $edicion
            );
        }
    }
}
