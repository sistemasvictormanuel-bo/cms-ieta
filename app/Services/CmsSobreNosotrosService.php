<?php

namespace App\Services;

use App\Models\CmsSobreNosotros;
use Illuminate\Support\Facades\DB;

class CmsSobreNosotrosService
{
    /**
     * Obtiene el registro único de Sobre Nosotros o crea uno por defecto si no existe.
     */
    public function obtener(): CmsSobreNosotros
    {
        return CmsSobreNosotros::firstOrCreate(
            ['id' => 1],
            [
                'sn_contenido' => ['blocks' => []],
                'sn_historia' => '',
                'sn_mision' => '',
                'sn_vision' => '',
                'sn_objetivos' => ['blocks' => []],
            ]
        );
    }

    /**
     * Actualiza el registro único institucional ($id = 1).
     */
    public function actualizar(array $data): CmsSobreNosotros
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtener();

            $registro->update([
                'sn_contenido' => $data['sn_contenido'] ?? $registro->sn_contenido,
                'sn_historia' => $data['sn_historia'] ?? $registro->sn_historia,
                'sn_mision' => $data['sn_mision'] ?? $registro->sn_mision,
                'sn_vision' => $data['sn_vision'] ?? $registro->sn_vision,
                'sn_objetivos' => $data['sn_objetivos'] ?? $registro->sn_objetivos,
            ]);

            DB::commit();

            return $registro->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
