<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModalidadCursoResource;
use App\Services\ModalidadesCursos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CursoModalidadController extends Controller
{
    public function __construct(
        protected ModalidadesCursos $modalidadService
    ) {}

    /**
     * Retorna el listado de modalidades de cursos.
     */
    public function index(): AnonymousResourceCollection
    {
        $modalidades = $this->modalidadService->obtenerTodos(null, 50);

        return ModalidadCursoResource::collection($modalidades);
    }

    /**
     * Retorna el detalle de una modalidad de curso por su ID.
     */
    public function show(int $id): ModalidadCursoResource|JsonResponse
    {
        try {
            $modalidad = $this->modalidadService->obtenerPorId($id);

            return new ModalidadCursoResource($modalidad);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Modalidad de curso no encontrada'], 404);
        }
    }
}
