<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsReglamentoInternoResource;
use App\Services\CmsReglamentoInternoService;
use Illuminate\Http\JsonResponse;

class CmsReglamentoInternoController extends Controller
{
    public function __construct(
        protected CmsReglamentoInternoService $reglamentoService
    ) {}

    /**
     * Retorna la información y archivo del reglamento interno para el portal web.
     */
    public function show(): JsonResponse
    {
        $reglamento = $this->reglamentoService->obtener();

        return response()->json([
            'data' => new CmsReglamentoInternoResource($reglamento),
        ]);
    }
}
