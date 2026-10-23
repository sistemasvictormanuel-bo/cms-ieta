<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AutoresPlantillaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function collection(): Collection
    {
        return collect([
            [
                'nombres' => 'Carlos Alberto',
                'apellido_paterno' => 'Condori',
                'apellido_materno' => 'Flores',
                'correo' => 'carlos.condori@umsa.bo',
                'celular' => '77712345',
                'grado_academico' => 'Doctorado',
                'tipo_autor' => 'Docente Investigador',
                'especialidad' => 'Bioestadística',
                'intereses_cientificos' => 'Bioestadística, Epidemiología Matemática, Modelos Bayesianos',
                'sitio_web' => 'https://orcid.org/0000-0001-2345-6789',
                'biografia' => 'Docente e investigador en el área de Bioestadística con más de 10 años de experiencia académica y proyectos de investigación aplicada.',
            ],
            [
                'nombres' => 'Elena Valeria',
                'apellido_paterno' => 'Rios',
                'apellido_materno' => 'Mendoza',
                'correo' => 'elena.rios@umsa.bo',
                'celular' => '72098765',
                'grado_academico' => 'Maestría',
                'tipo_autor' => 'Estudiante Investigador',
                'especialidad' => 'Ciencia de Datos',
                'intereses_cientificos' => 'Machine Learning, Procesamiento del Lenguaje Natural, Visualización de Datos',
                'sitio_web' => 'https://linkedin.com/in/elena-rios',
                'biografia' => 'Investigadora en formación especializada en modelos predictivos, análisis de datos estadísticos y desarrollo de herramientas computacionales.',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'correo',
            'celular',
            'grado_academico',
            'tipo_autor',
            'especialidad',
            'intereses_cientificos',
            'sitio_web',
            'biografia',
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
