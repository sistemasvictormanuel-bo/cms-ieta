<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstruccionAutorResource;
use App\Services\InstruccionAutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InstruccionAutorApiController extends Controller
{
    public function __construct(
        protected InstruccionAutorService $instruccionService
    ) {}

    /**
     * Retorna todas las directrices activas para autores ordenadas según la secuencia establecida.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $instrucciones = $this->instruccionService->obtenerActivos();

        return InstruccionAutorResource::collection($instrucciones);
    }

    /**
     * Retorna el detalle de una instrucción por ID.
     */
    public function show(int $id): InstruccionAutorResource|JsonResponse
    {
        try {
            $instruccion = $this->instruccionService->obtenerPorId($id);

            return new InstruccionAutorResource($instruccion);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Instrucción no encontrada'], 404);
        }
    }
}
