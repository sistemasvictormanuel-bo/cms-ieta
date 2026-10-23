<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsContactoResource;
use App\Services\CmsContactoService;
use Illuminate\Http\JsonResponse;

class ContactoController extends Controller
{
    public function __construct(
        protected CmsContactoService $contactoService
    ) {}

    /**
     * Devuelve el registro institucional único de Contacto.
     */
    public function show(): JsonResponse
    {
        $registro = $this->contactoService->obtener();

        return response()->json(new CmsContactoResource($registro), 200);
    }
}
