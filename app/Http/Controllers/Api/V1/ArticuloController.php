<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticuloResource;
use App\Services\ArticuloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticuloController extends Controller
{
    public function __construct(
        protected ArticuloService $articuloService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $tipoId = $request->filled('tipo_id') ? (int) $request->query('tipo_id') : null;
        $edicionId = $request->filled('edicion_id') ? (int) $request->query('edicion_id') : null;
        $autorId = $request->filled('autor_id')
            ? (int) $request->query('autor_id')
            : ($request->filled('autor') ? (int) $request->query('autor') : null);
        $autorSlug = $request->query('autor_slug');
        $etiquetaSlug = $request->query('etiqueta_slug') ?: $request->query('etiqueta');
        $perPage = (int) $request->query('per_page', 20);

        $articulos = $this->articuloService->obtenerPublicos(
            search: $search,
            tipoId: $tipoId,
            edicionId: $edicionId,
            perPage: $perPage,
            autorId: $autorId,
            autorSlug: $autorSlug,
            etiquetaSlug: $etiquetaSlug
        );

        return ArticuloResource::collection($articulos);
    }

    public function show(string|int $idOrSlug): ArticuloResource|JsonResponse
    {
        try {
            $articulo = is_numeric($idOrSlug)
                ? $this->articuloService->obtenerPorId((int) $idOrSlug)
                : $this->articuloService->obtenerPorSlug((string) $idOrSlug);

            if ($articulo->estado_revision !== 'aprobado' || ! $articulo->art_estado) {
                return response()->json(['message' => 'Artículo no disponible'], 404);
            }

            $this->articuloService->incrementarVistas($articulo->id);

            // Cargar recomendaciones en el recurso principal
            $similares = $this->articuloService->obtenerSimilares($articulo, 4);
            $masLeidos = $this->articuloService->obtenerMasLeidosMismoAutor($articulo, 4);

            $articulo->setRelation('articulosSimilares', $similares);
            $articulo->setRelation('masLeidosAutor', $masLeidos);

            return new ArticuloResource($articulo);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Artículo no encontrado'], 404);
        }
    }

    /**
     * Retorna exclusivamente la lista de artículos similares a un artículo dado.
     */
    public function similares(Request $request, string|int $idOrSlug): AnonymousResourceCollection|JsonResponse
    {
        try {
            $articulo = is_numeric($idOrSlug)
                ? $this->articuloService->obtenerPorId((int) $idOrSlug)
                : $this->articuloService->obtenerPorSlug((string) $idOrSlug);

            $limit = (int) $request->query('limit', 4);
            $similares = $this->articuloService->obtenerSimilares($articulo, $limit);

            return ArticuloResource::collection($similares);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Artículo no encontrado'], 404);
        }
    }

    /**
     * Retorna exclusivamente los artículos más leídos del mismo autor / coautores.
     */
    public function masLeidosAutor(Request $request, string|int $idOrSlug): AnonymousResourceCollection|JsonResponse
    {
        try {
            $articulo = is_numeric($idOrSlug)
                ? $this->articuloService->obtenerPorId((int) $idOrSlug)
                : $this->articuloService->obtenerPorSlug((string) $idOrSlug);

            $limit = (int) $request->query('limit', 4);
            $masLeidos = $this->articuloService->obtenerMasLeidosMismoAutor($articulo, $limit);

            return ArticuloResource::collection($masLeidos);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Artículo no encontrado'], 404);
        }
    }
}
