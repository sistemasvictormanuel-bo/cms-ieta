<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsServicioResource;
use App\Services\CmsServiciosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServicioController extends Controller
{
    public function __construct(
        protected CmsServiciosService $serviciosService
    ) {}

    /**
     * Retorna el listado completo de servicios institucionales.
     */
    public function index(): AnonymousResourceCollection
    {
        $servicios = $this->serviciosService->obtenerTodas(null, 50);

        return CmsServicioResource::collection($servicios);
    }

    /**
     * Retorna el detalle de un servicio específico por su ID.
     */
    public function show(int $id): CmsServicioResource|JsonResponse
    {
        $servicio = $this->serviciosService->obtenerPorId($id);

        if (! $servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        return new CmsServicioResource($servicio);
    }

    /**
     * Alias compatible con nomenclatura del proyecto.
     */
    public function serviciosTodos(): AnonymousResourceCollection
    {
        return $this->index();
    }

    /**
     * Alias compatible con nomenclatura del proyecto.
     */
    public function serviciosPorId(int $id): CmsServicioResource|JsonResponse
    {
        return $this->show($id);
    }
}
