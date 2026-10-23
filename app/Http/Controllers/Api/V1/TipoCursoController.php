<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TipoCursoResource;
use App\Services\TipoCursosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TipoCursoController extends Controller
{
    public function __construct(
        protected TipoCursosService $tipoCursosService
    ) {}

    /**
     * Retorna el listado de tipos de cursos.
     */
    public function index(): AnonymousResourceCollection
    {
        $tipos = $this->tipoCursosService->obtenerTodos(null, 50);

        return TipoCursoResource::collection($tipos);
    }

    /**
     * Retorna el detalle de un tipo de curso por su ID.
     */
    public function show(int $id): TipoCursoResource|JsonResponse
    {
        try {
            $tipo = $this->tipoCursosService->obtenerPorId($id);

            return new TipoCursoResource($tipo);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Tipo de curso no encontrado'], 404);
        }
    }
}
