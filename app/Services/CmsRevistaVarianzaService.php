<?php

namespace App\Services;

use App\Models\CmsRevistaVarianza;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsRevistaVarianzaService
{
    /**
     * Retorna el registro singleton de la información institucional de Revista Varianza.
     */
    public function obtener(): CmsRevistaVarianza
    {
        return CmsRevistaVarianza::firstOrCreate(
            ['id' => 1],
            [
                'rv_titulo' => 'Revista Varianza',
                'rv_subtitulo' => 'Publicación Científica Arbitrada del Instituto de Estadística Teórica y Aplicada',
                'rv_mision' => 'Publicar investigaciones rigurosas en estadística teórica, aplicada, econometría y ciencia de datos.',
                'rv_vision' => 'Consolidarse como una revista referente indexada internacionalmente.',
                'rv_issn_impreso' => '2076-6645',
                'rv_issn_electronico' => '2664-0899',
                'rv_periodicidad' => 'Semestral',
                'rv_correo_contacto' => 'revista.varianza@umsa.bo',
            ]
        );
    }

    /**
     * Actualiza la información institucional y la portada de la revista.
     */
    public function actualizar(array $data, ?UploadedFile $portada = null): CmsRevistaVarianza
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtener();

            $registro->update([
                'rv_titulo' => $data['rv_titulo'] ?? $registro->rv_titulo,
                'rv_subtitulo' => array_key_exists('rv_subtitulo', $data) ? $data['rv_subtitulo'] : $registro->rv_subtitulo,
                'rv_mision' => array_key_exists('rv_mision', $data) ? $data['rv_mision'] : $registro->rv_mision,
                'rv_vision' => array_key_exists('rv_vision', $data) ? $data['rv_vision'] : $registro->rv_vision,
                'rv_contenido' => array_key_exists('rv_contenido', $data) ? $data['rv_contenido'] : $registro->rv_contenido,
                'rv_alcance' => array_key_exists('rv_alcance', $data) ? $data['rv_alcance'] : $registro->rv_alcance,
                'rv_politicas' => array_key_exists('rv_politicas', $data) ? $data['rv_politicas'] : $registro->rv_politicas,
                'rv_issn_impreso' => array_key_exists('rv_issn_impreso', $data) ? $data['rv_issn_impreso'] : $registro->rv_issn_impreso,
                'rv_issn_electronico' => array_key_exists('rv_issn_electronico', $data) ? $data['rv_issn_electronico'] : $registro->rv_issn_electronico,
                'rv_periodicidad' => array_key_exists('rv_periodicidad', $data) ? $data['rv_periodicidad'] : $registro->rv_periodicidad,
                'rv_correo_contacto' => array_key_exists('rv_correo_contacto', $data) ? $data['rv_correo_contacto'] : $registro->rv_correo_contacto,
            ]);

            if ($portada) {
                $registro->clearMediaCollection('rv_portada');
                $registro->addMedia($portada)->toMediaCollection('rv_portada');
            }

            DB::commit();

            return $registro->fresh(['media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
