<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemoriasInvestigacionResource;
use App\Services\MemoriasInvestigacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemoriasInvestigacionController extends Controller
{
    public function __construct(
        protected MemoriasInvestigacionService $memoriasService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $autorId = $request->filled('autor_id') ? (int) $request->input('autor_id') : null;
        $etiqueta = $request->input('etiqueta');
        $perPage = (int) $request->input('per_page', 15);

        $datos = $this->memoriasService->obtenerTodos($search, $autorId, $etiqueta, $perPage);

        return MemoriasInvestigacionResource::collection($datos);
    }

    public function show(int $id): JsonResponse
    {
        $dato = $this->memoriasService->obtenerPorId($id);

        return response()->json([
            'data' => new MemoriasInvestigacionResource($dato),
        ]);
    }
}
