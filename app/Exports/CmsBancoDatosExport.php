<?php

namespace App\Exports;

use App\Models\CmsBancoDatos;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CmsBancoDatosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?Request $request = null
    ) {}

    public function collection(): Collection
    {
        $query = CmsBancoDatos::with('areasTematicas')->latest('id');

        if ($this->request) {
            if ($this->request->filled('tipo')) {
                $query->where('bdd_tipo_dato', $this->request->input('tipo'));
            }

            if ($this->request->filled('search')) {
                $term = trim($this->request->input('search'));
                $query->where(function ($q) use ($term) {
                    $q->orWhere('bdd_codigo', 'like', "%{$term}%")
                        ->orWhere('bdd_titulo', 'like', "%{$term}%")
                        ->orWhere('bdd_descripcion', 'like', "%{$term}%")
                        ->orWhere('bdd_locacion', 'like', "%{$term}%")
                        ->orWhere('bdd_anio', 'like', "%{$term}%")
                        ->orWhereHas('areasTematicas', function ($aq) use ($term) {
                            $aq->where('at_nombre', 'like', "%{$term}%");
                        });
                });
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tipo de Base',
            'Año',
            'Código de Identificación',
            'Título de la Base de Datos',
            'Locación / Cobertura',
            'Enlace Externo',
            'Áreas Temáticas',
            'Descripción Técnica',
            'Tiene Archivo',
            'Tiene Portada',
            'Fecha Registro',
        ];
    }

    /**
     * @param  CmsBancoDatos  $banco
     */
    public function map($banco): array
    {
        return [
            $banco->id,
            $banco->bdd_tipo_dato,
            $banco->bdd_anio,
            $banco->bdd_codigo,
            $banco->bdd_titulo,
            $banco->bdd_locacion ?? 'Bolivia',
            $banco->bdd_url ?? '',
            $banco->areasTematicas->isNotEmpty() ? $banco->areasTematicas->pluck('at_nombre')->implode(', ') : 'General',
            $banco->bdd_descripcion ?? '',
            $banco->hasMedia('bdd_archivo') ? 'Sí' : 'No',
            $banco->hasMedia('bdd_portada') ? 'Sí' : 'No',
            $banco->created_at?->format('d/m/Y H:i') ?? '',
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
