<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradoAcademicoResource;
use App\Services\GradoAcademicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GradoAcademicoController extends Controller
{
    public function __construct(
        protected GradoAcademicoService $gradoAcademicoService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $grados = $this->gradoAcademicoService->obtenerTodos(null, 50);

        return GradoAcademicoResource::collection($grados);
    }

    public function show(int $id): GradoAcademicoResource|JsonResponse
    {
        try {
            $grado = $this->gradoAcademicoService->obtenerPorId($id);

            return new GradoAcademicoResource($grado);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Grado académico no encontrado'], 404);
        }
    }
}
