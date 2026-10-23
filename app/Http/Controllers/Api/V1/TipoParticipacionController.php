<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TipoParticipacionResource;
use App\Services\TipoParticipacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TipoParticipacionController extends Controller
{
    public function __construct(
        protected TipoParticipacionService $tipoParticipacionService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $tipos = $this->tipoParticipacionService->obtenerTodos(null, 50);

        return TipoParticipacionResource::collection($tipos);
    }

    public function show(int $id): TipoParticipacionResource|JsonResponse
    {
        try {
            $tipo = $this->tipoParticipacionService->obtenerPorId($id);

            return new TipoParticipacionResource($tipo);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Tipo de participación no encontrado'], 404);
        }
    }
}
