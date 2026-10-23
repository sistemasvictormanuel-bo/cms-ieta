<?php

namespace Database\Seeders;

use App\Models\TipoArticulo;
use Illuminate\Database\Seeder;

class TipoArticuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'id' => 1,
                'tar_nombre' => 'Artículo Original',
                'tar_descripcion' => 'Trabajos de investigación empírica, metodológica o teórica inéditos que presentan aportes significativos.',
                'tar_estado' => true,
            ],
            [
                'id' => 2,
                'tar_nombre' => 'Nota Científica',
                'tar_descripcion' => 'Comunicaciones breves sobre resultados preliminares, avances de investigación o notas metodológicas.',
                'tar_estado' => true,
            ],
            [
                'id' => 3,
                'tar_nombre' => 'Artículo de Revisión',
                'tar_descripcion' => 'Revisiones bibliográficas críticas y exhaustivas sobre un tema específico de estadística o ciencia afín.',
                'tar_estado' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoArticulo::updateOrCreate(
                ['id' => $tipo['id']],
                $tipo
            );
        }
    }
}
