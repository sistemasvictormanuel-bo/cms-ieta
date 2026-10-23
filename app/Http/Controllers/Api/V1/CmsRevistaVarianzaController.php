<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsRevistaVarianzaResource;
use App\Services\CmsRevistaVarianzaService;
use Illuminate\Http\JsonResponse;

class CmsRevistaVarianzaController extends Controller
{
    public function __construct(
        protected CmsRevistaVarianzaService $revistaVarianzaService
    ) {}

    /**
     * Devuelve el perfil institucional único de la Revista Varianza (misión, visión, políticas, alcance, etc.).
     */
    public function show(): JsonResponse
    {
        $registro = $this->revistaVarianzaService->obtener();

        return response()->json(new CmsRevistaVarianzaResource($registro), 200);
    }
}
