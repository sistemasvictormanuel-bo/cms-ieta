<?php

namespace App\Console\Commands;

use App\Models\CmsLineasInvestigacion;
use App\Models\CmsServicios;
use App\Models\CmsSobreNosotros;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanEditorMediaUrls extends Command
{
    /**
     * Nombre y firma del comando en consola.
     *
     * @var string
     */
    protected $signature = 'app:clean-editor-urls';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Convierte URLs absolutas (localhost, 127.0.0.1) en rutas relativas (/storage/...) dentro de los campos Editor.js';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $this->info('Iniciando limpieza de URLs absolutas en campos de Editor.js...');

        $actualizados = 0;

        // 1. Limpieza en Líneas de Investigación
        $lineas = CmsLineasInvestigacion::all();
        foreach ($lineas as $linea) {
            $raw = $linea->getRawOriginal('li_contenido');
            if ($raw && is_string($raw)) {
                $cleaned = $this->cleanUrls($raw);
                if ($cleaned !== $raw) {
                    DB::table('cms_lineas_investigaciones')
                        ->where('id', $linea->id)
                        ->update(['li_contenido' => $cleaned]);
                    $actualizados++;
                }
            }
        }

        // 2. Limpieza en Servicios
        $servicios = CmsServicios::all();
        foreach ($servicios as $servicio) {
            $raw = $servicio->getRawOriginal('sv_contenido');
            if ($raw && is_string($raw)) {
                $cleaned = $this->cleanUrls($raw);
                if ($cleaned !== $raw) {
                    DB::table('cms_servicios')
                        ->where('id', $servicio->id)
                        ->update(['sv_contenido' => $cleaned]);
                    $actualizados++;
                }
            }
        }

        // 3. Limpieza en Sobre Nosotros
        $sobreNosotros = CmsSobreNosotros::all();
        foreach ($sobreNosotros as $sn) {
            $updates = [];

            $rawContenido = $sn->getRawOriginal('sn_contenido');
            if ($rawContenido && is_string($rawContenido)) {
                $cleaned = $this->cleanUrls($rawContenido);
                if ($cleaned !== $rawContenido) {
                    $updates['sn_contenido'] = $cleaned;
                }
            }

            $rawObjetivos = $sn->getRawOriginal('sn_objetivos');
            if ($rawObjetivos && is_string($rawObjetivos)) {
                $cleaned = $this->cleanUrls($rawObjetivos);
                if ($cleaned !== $rawObjetivos) {
                    $updates['sn_objetivos'] = $cleaned;
                }
            }

            if (! empty($updates)) {
                DB::table('cms_sobre_nosotros')
                    ->where('id', $sn->id)
                    ->update($updates);
                $actualizados++;
            }
        }

        $this->info("¡Limpieza finalizada! Se actualizaron {$actualizados} registros con URLs relativas.");

        return Command::SUCCESS;
    }

    /**
     * Reemplaza cualquier URL absoluta que apunte a /storage/ por la ruta relativa /storage/
     */
    protected function cleanUrls(string $jsonString): string
    {
        // Reemplaza http://localhost:8000/storage/, http://127.0.0.1:8000/storage/ o cualquier host
        return preg_replace('#https?://[^/]+/storage/#i', '/storage/', $jsonString);
    }
}
