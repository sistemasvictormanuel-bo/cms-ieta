<?php

namespace Database\Seeders;

use App\Models\EdicionVarianza;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EdicionVarianzaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/ediciones_varianza.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $ediciones = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($ediciones)) {
            return;
        }

        Model::unguard();

        foreach ($ediciones as $item) {
            $slug = $item['ev_slug'] ?? Str::slug($item['ev_titulo'] ?? ('Varianza N° '.$item['ev_numero']));

            EdicionVarianza::updateOrCreate(
                ['id' => $item['id']],
                [
                    'ev_numero' => $item['ev_numero'],
                    'ev_volumen' => $item['ev_volumen'] ?? null,
                    'ev_anio' => $item['ev_anio'] ?? null,
                    'ev_mes' => $item['ev_mes'] ?? null,
                    'ev_titulo' => $item['ev_titulo'] ?? null,
                    'ev_slug' => $slug,
                    'ev_issn_ver_impresa' => $item['ev_issn_ver_impresa'] ?? null,
                    'ev_issn_ver_electronica' => $item['ev_issn_ver_electronica'] ?? null,
                    'ev_num_art_originales' => $item['ev_num_art_originales'] ?? null,
                    'ev_notas_cientificas' => $item['ev_notas_cientificas'] ?? null,
                    'ev_num_paginas' => $item['ev_num_paginas'] ?? null,
                    'ev_doi' => $item['ev_doi'] ?? null,
                    'ev_presentacion' => $item['ev_presentacion'] ?? null,
                    'ev_editorial' => $item['ev_editorial'] ?? null,
                    'ev_fecha_publicacion' => $item['ev_fecha_publicacion'] ?? null,
                    'ev_estado' => (bool) ($item['ev_estado'] ?? true),
                    'ev_enlace_url' => $item['ev_enlace_url'] ?? null,
                ]
            );
        }

        Model::reguard();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('ediciones_varianzas', 'id'), COALESCE(MAX(id), 1)) FROM ediciones_varianzas;");
        }
    }
}
