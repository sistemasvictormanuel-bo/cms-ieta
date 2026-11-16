<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\EtiquetaArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticulosSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/articulos.json';

        if (! file_exists($jsonPath)) {
            return;
        }

        $articulos = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($articulos)) {
            return;
        }

        foreach ($articulos as $item) {
            $articulo = Articulo::updateOrCreate(
                ['id' => $item['id']],
                [
                    'art_titulo' => $item['art_titulo'],
                    'art_slug' => $item['art_slug'] ?? Str::slug($item['art_titulo']),
                    'art_resumen' => $item['art_resumen'] ?? null,
                    'art_abstracto' => $item['art_abstracto'] ?? null,
                    'art_fecha_recepcion' => $item['art_fecha_recepcion'] ?? null,
                    'art_fecha_aceptado' => $item['art_fecha_aceptado'] ?? null,
                    'art_orcid' => $item['art_orcid'] ?? null,
                    'art_doi' => $item['art_doi'] ?? null,
                    'art_pag_inicio' => $item['art_pag_inicio'] ?? null,
                    'art_pag_fin' => $item['art_pag_fin'] ?? null,
                    'art_num_paginas' => $item['art_num_paginas'] ?? null,
                    'art_estado' => (bool) ($item['art_estado'] ?? true),
                    'art_referencias' => $item['art_referencias'] ?? null,
                    'art_enlace_url' => $item['art_enlace_url'] ?? null,
                    'art_vistas' => $item['art_vistas'] ?? 0,
                    'tipo_articulo_id' => $item['tipo_articulo_id'] ?? null,
                    'edicion_varianza_id' => $item['edicion_varianza_id'] ?? null,
                    'estado_revision' => $item['estado_revision'] ?? 'aprobado',
                ]
            );

            // 1. Sincronizar Edición Pivot
            if (! empty($item['edicion_varianza_id'])) {
                $articulo->edicionesPivot()->syncWithoutDetaching([
                    $item['edicion_varianza_id'] => [
                        'aev_pag_inicio' => $item['art_pag_inicio'] ?? null,
                        'aev_pag_fin' => $item['art_pag_fin'] ?? null,
                    ],
                ]);
            }

            // 2. Sincronizar Autores Pivot
            if (! empty($item['autores_pivot']) && is_array($item['autores_pivot'])) {
                $syncAutores = [];
                foreach ($item['autores_pivot'] as $aut) {
                    $syncAutores[$aut['autor_id']] = [
                        'tipo_participaciones_id' => $aut['tipo_participaciones_id'] ?? 1,
                    ];
                }
                $articulo->autores()->sync($syncAutores);
            }

            // 3. Sincronizar Etiquetas
            if (! empty($item['etiquetas']) && is_array($item['etiquetas'])) {
                $tagIds = [];
                foreach ($item['etiquetas'] as $tagNombre) {
                    $tagSlug = Str::slug($tagNombre);
                    $etiqueta = EtiquetaArt::where('ea_slug', $tagSlug)
                        ->orWhereRaw('LOWER(TRIM(ea_nombre)) = ?', [mb_strtolower($tagNombre)])
                        ->first();

                    if (! $etiqueta) {
                        $etiqueta = EtiquetaArt::create([
                            'ea_nombre' => $tagNombre,
                            'ea_slug' => $tagSlug ?: 'tag-'.uniqid(),
                        ]);
                    }
                    $tagIds[] = $etiqueta->id;
                }
                if (! empty($tagIds)) {
                    $articulo->etiquetas()->sync($tagIds);
                }
            }
        }
    }
}
