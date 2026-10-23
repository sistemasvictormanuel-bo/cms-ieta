<?php

namespace App\Exports;

use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArticulosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?Request $request = null
    ) {}

    public function collection(): Collection
    {
        $query = Articulo::with([
            'media',
            'tipoArticulo',
            'edicion',
            'etiquetas',
            'autores.persona',
            'autores.gradoAcademico',
        ])->latest('id');

        if ($this->request) {
            if ($this->request->filled('tipo_id')) {
                $query->where('tipo_articulo_id', (int) $this->request->input('tipo_id'));
            }

            if ($this->request->filled('edicion_id')) {
                $query->where('edicion_varianza_id', (int) $this->request->input('edicion_id'));
            }

            if ($this->request->filled('search')) {
                $term = trim($this->request->input('search'));
                $query->where(function ($q) use ($term) {
                    $q->orWhere('art_titulo', 'like', "%{$term}%")
                        ->orWhere('art_doi', 'like', "%{$term}%")
                        ->orWhere('art_orcid', 'like', "%{$term}%");
                });
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Tipo de Artículo',
            'Edición',
            'Pág. Inicio',
            'Pág. Fin',
            'N° Páginas',
            'DOI',
            'ORCID',
            'Fecha Recepción',
            'Fecha Aceptado',
            'Autores',
            'Etiquetas / Palabras Clave',
            'Resumen',
            'Abstract',
            'Referencias',
            'Archivo URL',
        ];
    }

    /**
     * @param  Articulo  $articulo
     */
    public function map($articulo): array
    {
        $autores = $articulo->autores->map(function ($autor) {
            $nombre = $autor->persona ? $autor->persona->nombre_completo : 'Autor';
            $grado = $autor->gradoAcademico ? " ({$autor->gradoAcademico->ga_abreviatura})" : '';

            return $nombre.$grado;
        })->implode('; ');

        $etiquetas = $articulo->etiquetas->pluck('ea_nombre')->implode(', ');

        $edicion = $articulo->edicion ? ($articulo->edicion->ev_numero ? "N° {$articulo->edicion->ev_numero} - {$articulo->edicion->ev_titulo}" : $articulo->edicion->ev_titulo) : 'Sin edición asignada';

        return [
            $articulo->id,
            $articulo->art_titulo,
            $articulo->tipoArticulo?->tar_nombre ?? 'Sin tipo',
            $edicion,
            $articulo->art_pag_inicio,
            $articulo->art_pag_fin,
            $articulo->art_num_paginas,
            $articulo->art_doi,
            $articulo->art_orcid,
            $articulo->art_fecha_recepcion?->format('Y-m-d'),
            $articulo->art_fecha_aceptado?->format('Y-m-d'),
            $autores,
            $etiquetas,
            $articulo->art_resumen,
            $articulo->art_abstracto,
            $articulo->art_referencias,
            $articulo->archivo_url ? url($articulo->archivo_url) : 'Sin archivo',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'], // Azul marino institucional
                ],
            ],
        ];
    }
}
