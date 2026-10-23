<?php

namespace App\Exports;

use App\Models\Autor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AutoresExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?Request $request = null
    ) {}

    public function collection(): Collection
    {
        $query = Autor::with([
            'persona',
            'tipoAutor',
            'especialidades',
            'gradoAcademico',
        ])->latest('id');

        if ($this->request && $this->request->filled('search')) {
            $term = trim($this->request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->orWhere('at_slug', 'like', "%{$term}%")
                    ->orWhere('at_intereses_cientificos', 'like', "%{$term}%")
                    ->orWhereHas('persona', function ($pq) use ($term) {
                        $pq->where('p_nombre', 'like', "%{$term}%")
                            ->orWhere('p_apellido_paterno', 'like', "%{$term}%")
                            ->orWhere('p_apellido_materno', 'like', "%{$term}%")
                            ->orWhere('p_correo', 'like', "%{$term}%");
                    });
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombres',
            'Apellido Paterno',
            'Apellido Materno',
            'Nombre Completo',
            'Correo Electrónico',
            'Celular',
            'Grado Académico',
            'Tipo de Autor',
            'Especialidad',
            'Intereses Científicos',
            'Sitio Web',
            'Biografía',
            'Estado',
            'Total Artículos Publicados',
        ];
    }

    /**
     * @param  Autor  $autor
     */
    public function map($autor): array
    {
        $persona = $autor->persona;

        $biografiaTexto = '';
        if (! empty($autor->at_biografia['blocks']) && is_array($autor->at_biografia['blocks'])) {
            $biografiaTexto = collect($autor->at_biografia['blocks'])
                ->pluck('data.text')
                ->filter()
                ->map(fn ($text) => trim(strip_tags(html_entity_decode((string) $text))))
                ->filter()
                ->implode("\n\n");
        }

        return [
            $autor->id,
            $persona?->p_nombre ?? '',
            $persona?->p_apellido_paterno ?? '',
            $persona?->p_apellido_materno ?? '',
            $persona?->nombre_completo ?? '',
            $persona?->p_correo ?? '',
            $persona?->p_celular ?? '',
            $autor->gradoAcademico?->ga_nombre ?? 'No especificado',
            $autor->tipoAutor?->ta_nombre ?? 'No especificado',
            $autor->especialidades->isNotEmpty() ? $autor->especialidades->pluck('ep_nombre')->implode(', ') : 'No especificada',
            $autor->at_intereses_cientificos ?? '',
            $persona?->p_sitio_web ?? '',
            $biografiaTexto,
            $autor->at_estado ? 'Activo' : 'Inactivo',
            $autor->articulos()->count(),
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
