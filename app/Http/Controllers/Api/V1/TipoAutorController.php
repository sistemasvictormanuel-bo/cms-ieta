<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TipoAutorResource;
use App\Services\TipoAutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TipoAutorController extends Controller
{
    public function __construct(
        protected TipoAutorService $tipoAutorService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $tipos = $this->tipoAutorService->obtenerTodos(null, 50);

        return TipoAutorResource::collection($tipos);
    }

    public function show(int $id): TipoAutorResource|JsonResponse
    {
        try {
            $tipo = $this->tipoAutorService->obtenerPorId($id);

            return new TipoAutorResource($tipo);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Tipo de autor no encontrado'], 404);
        }
    }
}
