<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsConfiguracionResource;
use App\Services\CmsConfiguracionService;

class CmsConfiguracionController extends Controller
{
    public function __construct(
        protected CmsConfiguracionService $configuracionService
    ) {}

    /**
     * Retorna la configuración general, identidad institucional, logos y redes sociales del IETA.
     */
    public function show(): CmsConfiguracionResource
    {
        $configuracion = $this->configuracionService->obtener();

        return new CmsConfiguracionResource($configuracion);
    }
}
