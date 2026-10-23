<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EspecialidadResource;
use App\Services\EspecialidadesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EspecialidadController extends Controller
{
    public function __construct(
        protected EspecialidadesService $especialidadesService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $especialidades = $this->especialidadesService->obtenerTodos(null, 50);

        return EspecialidadResource::collection($especialidades);
    }

    public function show(int $id): EspecialidadResource|JsonResponse
    {
        try {
            $especialidad = $this->especialidadesService->obtenerPorId($id);

            return new EspecialidadResource($especialidad);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }
    }
}
