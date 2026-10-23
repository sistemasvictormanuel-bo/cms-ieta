<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EtiquetaArtResource;
use App\Services\EtiquetaArtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EtiquetaArtApiController extends Controller
{
    public function __construct(
        protected EtiquetaArtService $etiquetaService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 30);

        $etiquetas = $this->etiquetaService->obtenerTodos($search, $perPage);

        return EtiquetaArtResource::collection($etiquetas);
    }

    public function show(string|int $idOrSlug): EtiquetaArtResource|JsonResponse
    {
        try {
            $etiqueta = is_numeric($idOrSlug)
                ? $this->etiquetaService->obtenerPorId((int) $idOrSlug)
                : $this->etiquetaService->obtenerPorSlug((string) $idOrSlug);

            return new EtiquetaArtResource($etiqueta);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Etiqueta no encontrada'], 404);
        }
    }
}
