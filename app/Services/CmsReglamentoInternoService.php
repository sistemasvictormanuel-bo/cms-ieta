<?php

namespace App\Services;

use App\Models\CmsReglamentoInterno;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsReglamentoInternoService
{
    /**
     * Obtiene el registro único de Reglamento Interno o lo crea con valores por defecto.
     */
    public function obtener(): CmsReglamentoInterno
    {
        return CmsReglamentoInterno::with('media')->firstOrCreate(
            ['id' => 1],
            [
                'reg_titulo' => 'Reglamento Interno Institucional',
                'reg_descripcion' => 'Normativa y disposiciones internas vigentes del Instituto de Estadística Teórica y Aplicada (IETA).',
                'reg_contenido' => ['blocks' => []],
            ]
        );
    }

    /**
     * Actualiza el registro único y gestiona la carga opcional del archivo PDF.
     */
    public function actualizar(array $data, ?UploadedFile $archivoPdf = null): CmsReglamentoInterno
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtener();

            $registro->update([
                'reg_titulo' => $data['reg_titulo'] ?? $registro->reg_titulo,
                'reg_descripcion' => $data['reg_descripcion'] ?? $registro->reg_descripcion,
                'reg_contenido' => $data['reg_contenido'] ?? $registro->reg_contenido,
            ]);

            if ($archivoPdf) {
                $registro->addMedia($archivoPdf)->toMediaCollection('reg_archivo');
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina el archivo PDF adjunto al reglamento.
     */
    public function eliminarArchivo(): CmsReglamentoInterno
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtener();
            $registro->clearMediaCollection('reg_archivo');

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
