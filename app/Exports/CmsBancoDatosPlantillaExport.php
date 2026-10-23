<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CmsBancoDatosPlantillaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function collection(): Collection
    {
        return collect([
            [
                'tipo_dato' => 'Censos',
                'anio' => 1992,
                'codigo' => 'cs92v1',
                'titulo' => 'INE - Censo Nacional de Población y Vivienda 1992',
                'locacion' => 'Bolivia',
                'url' => 'https://www.ine.gob.bo/censo1992',
                'areas_tematicas' => 'Demografía, Población y Vivienda',
                'descripcion' => 'Base de datos oficial con microdatos del Censo Nacional de Población y Vivienda 1992 en Bolivia a nivel municipal y departamental.',
            ],
            [
                'tipo_dato' => 'Censos',
                'anio' => 2012,
                'codigo' => 'cs12v1',
                'titulo' => 'INE - Censo Nacional de Población y Vivienda 2012',
                'locacion' => 'Bolivia',
                'url' => 'https://www.ine.gob.bo/censo2012',
                'areas_tematicas' => 'Demografía, Vivienda, Servicios Básicos',
                'descripcion' => 'Resultados y microdatos armonizados del Censo de Población y Vivienda 2012.',
            ],
            [
                'tipo_dato' => 'Encuestas',
                'anio' => 2023,
                'codigo' => 'eh2023v1',
                'titulo' => 'INE - Encuesta de Hogares 2023 (EH-2023)',
                'locacion' => 'Bolivia',
                'url' => 'https://www.ine.gob.bo/encuesta-hogares-2023',
                'areas_tematicas' => 'Pobreza, Empleo, Ingresos, Condiciones de Vida',
                'descripcion' => 'Microdatos anuales de la Encuesta de Hogares 2023 para medición de pobreza multidimensional, empleo e ingresos laborales.',
            ],
            [
                'tipo_dato' => 'Encuestas',
                'anio' => 2024,
                'codigo' => 'eh2024v1',
                'titulo' => 'INE - Encuesta Continua de Empleo 2024 (ECE)',
                'locacion' => 'Bolivia',
                'url' => 'https://www.ine.gob.bo/ece-2024',
                'areas_tematicas' => 'Mercado Laboral, Ocupación, Desempleo',
                'descripcion' => 'Indicadores trimestrales y microdatos sobre el mercado laboral y tasa de desempleo en áreas urbanas y rurales.',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'tipo_dato',
            'anio',
            'codigo',
            'titulo',
            'locacion',
            'url',
            'areas_tematicas',
            'descripcion',
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
                    'startColor' => ['rgb' => '1E3A8A'],
                ],
            ],
        ];
    }
}
