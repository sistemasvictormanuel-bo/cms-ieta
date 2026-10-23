<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsSobreNosotrosResource;
use App\Services\CmsSobreNosotrosService;
use Illuminate\Http\JsonResponse;

class SobreNosotrosController extends Controller
{
    public function __construct(
        protected CmsSobreNosotrosService $sobreNosotrosService
    ) {}

    /**
     * Devuelve el registro institucional único de Sobre Nosotros.
     */
    public function show(): JsonResponse
    {
        $registro = $this->sobreNosotrosService->obtener();

        return response()->json(new CmsSobreNosotrosResource($registro), 200);
    }
}
