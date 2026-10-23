<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsBancoDatosResource;
use App\Services\CmsBancoDatosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsBancoDatosController extends Controller
{
    public function __construct(
        protected CmsBancoDatosService $bancoDatosService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');
        $perPage = (int) $request->input('per_page', 15);

        $datos = $this->bancoDatosService->obtenerTodos($search, $tipo, $perPage);

        return CmsBancoDatosResource::collection($datos);
    }

    public function show(int $id): JsonResponse
    {
        $dato = $this->bancoDatosService->obtenerPorId($id);

        return response()->json([
            'data' => new CmsBancoDatosResource($dato),
        ]);
    }
}
