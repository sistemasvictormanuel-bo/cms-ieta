<?php

namespace App\Services;

use App\Models\CmsConfiguracion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsConfiguracionService
{
    public function obtener(): CmsConfiguracion
    {
        $config = CmsConfiguracion::with('media')->first();

        if (! $config) {
            $config = CmsConfiguracion::create([
                'cfg_nombre_institucion' => 'Instituto de Estadística Teórica y Aplicada',
                'cfg_siglas' => 'IETA',
                'cfg_slogan' => 'Investigación, rigor científico y análisis estadístico',
                'cfg_descripcion_corta' => 'Instituto de investigación científica adscrito a la Universidad Mayor de San Andrés (UMSA), dedicado al avance teórico y práctico de la ciencia estadística.',
                'cfg_email_principal' => 'contacto@ieta.umsa.bo',
                'cfg_telefono_principal' => '+591 2 2441234',
                'cfg_direccion_principal' => 'Campus Universitario Cota Cota, La Paz - Bolivia',
                'cfg_redes_sociales' => [
                    'facebook' => 'https://facebook.com/ieta.umsa',
                    'twitter' => '',
                    'linkedin' => '',
                    'youtube' => '',
                    'instagram' => '',
                ],
                'cfg_copyright' => '© '.date('Y').' Instituto de Estadística Teórica y Aplicada (IETA). UMSA. Todos los derechos reservados.',
            ]);
        }

        return $config->load('media');
    }

    public function actualizar(
        array $data,
        ?UploadedFile $logoPrincipal = null,
        ?UploadedFile $logoBlanco = null,
        ?UploadedFile $favicon = null
    ): CmsConfiguracion {
        try {
            DB::beginTransaction();

            $config = $this->obtener();

            $config->update([
                'cfg_nombre_institucion' => $data['cfg_nombre_institucion'] ?? $config->cfg_nombre_institucion,
                'cfg_siglas' => $data['cfg_siglas'] ?? $config->cfg_siglas,
                'cfg_slogan' => array_key_exists('cfg_slogan', $data) ? $data['cfg_slogan'] : $config->cfg_slogan,
                'cfg_descripcion_corta' => array_key_exists('cfg_descripcion_corta', $data) ? $data['cfg_descripcion_corta'] : $config->cfg_descripcion_corta,
                'cfg_email_principal' => array_key_exists('cfg_email_principal', $data) ? $data['cfg_email_principal'] : $config->cfg_email_principal,
                'cfg_telefono_principal' => array_key_exists('cfg_telefono_principal', $data) ? $data['cfg_telefono_principal'] : $config->cfg_telefono_principal,
                'cfg_direccion_principal' => array_key_exists('cfg_direccion_principal', $data) ? $data['cfg_direccion_principal'] : $config->cfg_direccion_principal,
                'cfg_redes_sociales' => $data['cfg_redes_sociales'] ?? $config->cfg_redes_sociales,
                'cfg_copyright' => array_key_exists('cfg_copyright', $data) ? $data['cfg_copyright'] : $config->cfg_copyright,
            ]);

            if ($logoPrincipal) {
                $config->clearMediaCollection('cfg_logo_principal');
                $config->addMedia($logoPrincipal)->toMediaCollection('cfg_logo_principal');
            }

            if ($logoBlanco) {
                $config->clearMediaCollection('cfg_logo_blanco');
                $config->addMedia($logoBlanco)->toMediaCollection('cfg_logo_blanco');
            }

            if ($favicon) {
                $config->clearMediaCollection('cfg_favicon');
                $config->addMedia($favicon)->toMediaCollection('cfg_favicon');
            }

            DB::commit();

            return $config->fresh('media');
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
