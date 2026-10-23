<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsComunicadoResource;
use App\Services\CmsComunicadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsComunicadoController extends Controller
{
    public function __construct(
        protected CmsComunicadoService $comunicadoService
    ) {}

    /**
     * Retorna el listado de comunicados activos y vigentes.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $tipo = $request->query('tipo');
        $perPage = (int) $request->query('per_page', 10);

        // Sólo comunicados activos para el consumo público
        $comunicados = $this->comunicadoService->obtenerTodos($search, true, $tipo, $perPage);

        return CmsComunicadoResource::collection($comunicados);
    }

    /**
     * Retorna el comunicado activo más reciente para mostrar en el Popup Modal de la web pública.
     */
    public function activo(): JsonResponse
    {
        $comunicado = $this->comunicadoService->obtenerActivo();

        if (! $comunicado) {
            return response()->json([
                'data' => null,
                'message' => 'No hay comunicados activos vigentes.',
            ], 200);
        }

        return response()->json([
            'data' => new CmsComunicadoResource($comunicado),
        ]);
    }

    /**
     * Retorna el detalle de un comunicado específico.
     */
    public function show(int $id): CmsComunicadoResource|JsonResponse
    {
        try {
            $comunicado = $this->comunicadoService->obtenerPorId($id);

            return new CmsComunicadoResource($comunicado);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Comunicado no encontrado.'], 404);
        }
    }
}
