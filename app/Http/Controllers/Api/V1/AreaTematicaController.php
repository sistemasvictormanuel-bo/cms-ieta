<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaTematicaResource;
use App\Services\AreaTematicasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AreaTematicaController extends Controller
{
    public function __construct(
        protected AreaTematicasService $areaTematicasService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $areas = $this->areaTematicasService->obtenerTodos($search, $perPage);

        return AreaTematicaResource::collection($areas);
    }

    public function show(int $id): JsonResponse
    {
        $area = $this->areaTematicasService->obtenerPorId($id);

        return response()->json([
            'data' => new AreaTematicaResource($area),
        ]);
    }
}
