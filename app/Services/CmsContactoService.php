<?php

namespace App\Services;

use App\Models\CmsContacto;
use Illuminate\Support\Facades\DB;

class CmsContactoService
{
    /**
     * Obtiene el registro institucional único de Contacto o inicializa uno por defecto si no existe.
     */
    public function obtener(): CmsContacto
    {
        return CmsContacto::firstOrCreate(
            ['id' => 1],
            [
                'cc_telefono' => ['+591 2 2441234'],
                'cc_dia_inicio' => 'Lunes',
                'cc_dia_fin' => 'Viernes',
                'cc_hora_apertura' => '08:30',
                'cc_hora_cierre' => '18:30',
                'cc_direccion' => 'Av. Villazón N° 1995, Edificio Central IETA, La Paz - Bolivia',
                'cc_email' => ['contacto@ieta.edu.bo'],
                'cc_contenido' => ['blocks' => []],
            ]
        );
    }

    /**
     * Actualiza el registro institucional único de Contacto ($id = 1).
     */
    public function actualizar(array $data): CmsContacto
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtener();

            $telefonos = isset($data['cc_telefono'])
                ? array_values(array_filter($data['cc_telefono'], fn ($t) => ! empty(trim((string) $t))))
                : $registro->cc_telefono;

            $emails = isset($data['cc_email'])
                ? array_values(array_filter($data['cc_email'], fn ($e) => ! empty(trim((string) $e))))
                : $registro->cc_email;

            $registro->update([
                'cc_telefono' => $telefonos,
                'cc_dia_inicio' => $data['cc_dia_inicio'] ?? $registro->cc_dia_inicio,
                'cc_dia_fin' => $data['cc_dia_fin'] ?? $registro->cc_dia_fin,
                'cc_hora_apertura' => $data['cc_hora_apertura'] ?? $registro->cc_hora_apertura,
                'cc_hora_cierre' => $data['cc_hora_cierre'] ?? $registro->cc_hora_cierre,
                'cc_direccion' => array_key_exists('cc_direccion', $data)
                    ? $data['cc_direccion']
                    : $registro->cc_direccion,
                'cc_email' => $emails,
                'cc_contenido' => array_key_exists('cc_contenido', $data)
                    ? $data['cc_contenido']
                    : $registro->cc_contenido,
            ]);

            DB::commit();

            return $registro->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
