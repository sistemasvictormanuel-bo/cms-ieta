<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemoriasInvestigacionPlantillaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function collection(): Collection
    {
        return collect([
            [
                'titulo' => 'Análisis estadístico del crecimiento poblacional estudiantil en la Universidad Mayor de San Andrés',
                'autor' => 'M.Sc. Nicolás Chávez Quisbert',
                'gestion' => 2015,
                'etiqueta' => 'Proyecto de Investigación',
                'descripcion' => 'Aprobado con Resolución del HCF Nº 780/2015. Documento en físico existente en los archivos de la Carrera.',
                'enlace_url' => 'https://drive.google.com/file/d/1k0-XxsSjMAefO9nJYz6JispbUhxPDWNN/view',
            ],
            [
                'titulo' => 'Curso Computacional modelación y Simulación de Series de Tiempo Univariadas',
                'autor' => 'M.Sc. Nicolás Chávez Quisbert',
                'gestion' => 2015,
                'etiqueta' => 'Proyecto de Interacción Social',
                'descripcion' => 'Resolución del HCF Nº 780/2015. Proyecto de Interacción Social entregado como informe físico.',
                'enlace_url' => '',
            ],
            [
                'titulo' => 'Medición de la Disparidad Salarial en las Regiones del Departamento de La Paz',
                'autor' => 'Lic. Melisa Abalos Choque',
                'gestion' => 2015,
                'etiqueta' => 'Proyecto de Investigación',
                'descripcion' => 'Aprobado con Resolución del HCF Nº 780/2015. Se encuentra el proyecto en físico.',
                'enlace_url' => '',
            ],
            [
                'titulo' => 'Modelos Autoregresivos Bayesianos (BVAR) aplicados a series macroeconómicas',
                'autor' => 'Lic. Genaro Llusco, Lic. Lizeth Mendoza Pinto',
                'gestion' => 2016,
                'etiqueta' => 'Proyecto de Investigación',
                'descripcion' => 'Proyecto de investigación con perfil e informes trimestrales.',
                'enlace_url' => '',
            ],
            [
                'titulo' => 'Aplicación de las Series de Tiempo Univariadas para la Modelación de Datos del COVID en Bolivia',
                'autor' => 'M.Sc. Nicolás Chávez Quisbert',
                'gestion' => 2023,
                'etiqueta' => 'Proyecto de Investigación',
                'descripcion' => 'Modelación estocástica y proyecciones de contagios en el departamento de La Paz.',
                'enlace_url' => 'https://drive.google.com/file/d/1FxCXo_p6F1_RNahq4XRtmhg0retgldmB/view',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'titulo',
            'autor',
            'gestion',
            'etiqueta',
            'descripcion',
            'enlace_url',
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
