<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArticulosPlantillaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function collection(): Collection
    {
        return collect([
            [
                'titulo' => 'Modelos de Regresión Cuantílica en Series Temporales de Inflación',
                'tipo_articulo' => 'Artículo Original',
                'edicion' => '25',
                'pag_inicio' => 10,
                'pag_fin' => 25,
                'doi' => '10.5377/varianza.v25i1.1234',
                'orcid' => '0000-0002-1825-0097',
                'fecha_recepcion' => '2024-03-15',
                'fecha_aceptado' => '2024-06-20',
                'autores' => 'Juan Carlos Pérez Condori; María Quispe Ramos',
                'etiquetas' => 'Series de Tiempo, Regresión Cuantílica, Inflación',
                'resumen' => 'En este artículo se analiza el comportamiento de la inflación mediante modelos autorregresivos cuantílicos aplicados a datos de la economía boliviana...',
                'abstract' => 'In this article, inflation behavior is analyzed through quantile autoregressive models applied to data from the Bolivian economy...',
                'referencias' => 'Koenker, R. (2005). Quantile Regression. Cambridge University Press; Greene, W. (2018). Econometric Analysis.',
            ],
            [
                'titulo' => 'Estimación de Áreas Pequeñas para Indicadores de Pobreza Multidimensional',
                'tipo_articulo' => 'Nota Científica',
                'edicion' => '25',
                'pag_inicio' => 26,
                'pag_fin' => 38,
                'doi' => '10.5377/varianza.v25i1.1235',
                'orcid' => '0000-0003-4567-8901',
                'fecha_recepcion' => '2024-04-02',
                'fecha_aceptado' => '2024-07-10',
                'autores' => 'Carlos Alberto Mamani',
                'etiquetas' => 'Áreas Pequeñas, Encuesta de Hogares, Pobreza',
                'resumen' => 'Se presentan estimadores empíricos de Bayes para desagregaciones municipales basadas en encuestas complejas y censos.',
                'abstract' => 'Empirical Bayes estimators for municipal disaggregations based on complex surveys and censuses are presented.',
                'referencias' => 'Rao, J. N. K., & Molina, I. (2015). Small Area Estimation. John Wiley & Sons.',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'titulo',
            'tipo_articulo',
            'edicion',
            'pag_inicio',
            'pag_fin',
            'doi',
            'orcid',
            'fecha_recepcion',
            'fecha_aceptado',
            'autores',
            'etiquetas',
            'resumen',
            'abstract',
            'referencias',
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
