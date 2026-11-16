<?php

namespace App\Exports;

use App\Models\MemoriasInvestigacion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemoriasInvestigacionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?Request $request = null
    ) {}

    public function collection(): Collection
    {
        $query = MemoriasInvestigacion::with(['autor.persona', 'media'])->latest('id');

        if ($this->request) {
            if ($this->request->filled('autor_id')) {
                $query->where('autor_id', (int) $this->request->input('autor_id'));
            }

            if ($this->request->filled('etiqueta')) {
                $query->where('mdi_etiqueta', trim($this->request->input('etiqueta')));
            }

            if ($this->request->filled('search')) {
                $term = trim($this->request->input('search'));
                $query->where(function ($q) use ($term) {
                    if (is_numeric($term)) {
                        $q->orWhere('id', (int) $term)
                            ->orWhere('mdi_anio', (int) $term);
                    }
                    $q->orWhere('mdi_titulo', 'like', "%{$term}%")
                        ->orWhere('mdi_descripcion', 'like', "%{$term}%")
                        ->orWhere('mdi_etiqueta', 'like', "%{$term}%")
                        ->orWhere('mdi_enlace_url', 'like', "%{$term}%")
                        ->orWhereHas('autor.persona', function ($pq) use ($term) {
                            $pq->where('p_nombre', 'like', "%{$term}%")
                                ->orWhere('p_apellido_paterno', 'like', "%{$term}%")
                                ->orWhere('p_apellido_materno', 'like', "%{$term}%");
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
            'Título de la Investigación / Memoria',
            'Investigador / Autor',
            'Correo del Investigador',
            'Gestión / Año',
            'Etiqueta / Tipo',
            'Descripción / Abstract',
            'Enlace Externo (Drive / Repositorio)',
            'Documento en Servidor',
            'Fecha de Registro',
        ];
    }

    public function map($row): array
    {
        /** @var MemoriasInvestigacion $row */
        $autorNombre = $row->autor?->persona
            ? trim("{$row->autor->persona->p_nombre} {$row->autor->persona->p_apellido_paterno} {$row->autor->persona->p_apellido_materno}")
            : 'Sin autor';

        return [
            $row->id,
            $row->mdi_titulo,
            $autorNombre,
            $row->autor?->persona?->p_correo ?? '',
            $row->mdi_anio,
            $row->mdi_etiqueta,
            $row->mdi_descripcion ?? '',
            $row->mdi_enlace_url ?? '',
            $row->archivo_url ?? '',
            $row->created_at?->format('d/m/Y H:i') ?? '',
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
