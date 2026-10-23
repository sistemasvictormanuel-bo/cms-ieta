<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsGaleriaResource;
use App\Services\CmsGaleriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsGaleriaController extends Controller
{
    public function __construct(
        protected CmsGaleriaService $galeriaService
    ) {}

    /**
     * Lista de imágenes para el portal web con filtros de búsqueda y categoría.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $categoria = $request->input('categoria');
        $destacado = $request->has('destacado') && $request->input('destacado') !== ''
            ? filter_var($request->input('destacado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        // Por defecto en la API pública se muestran sólo imágenes activas
        $activo = $request->has('activo') && $request->input('activo') !== ''
            ? filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : true;

        $perPage = (int) $request->input('per_page', 12);

        $imagenes = $this->galeriaService->obtenerTodos(
            $search,
            $categoria,
            $destacado,
            $activo,
            $perPage
        );

        return CmsGaleriaResource::collection($imagenes);
    }

    /**
     * Listado de imágenes destacadas para carruseles o cabeceras del portal.
     */
    public function destacados(Request $request): AnonymousResourceCollection
    {
        $limit = (int) $request->input('limit', 6);
        $categoria = $request->input('categoria');

        $imagenes = $this->galeriaService->obtenerParaApi($limit, $categoria, true);

        return CmsGaleriaResource::collection($imagenes);
    }

    /**
     * Lista de categorías disponibles con cantidad de imágenes activas.
     */
    public function categorias(): JsonResponse
    {
        $categorias = $this->galeriaService->obtenerCategorias();

        return response()->json([
            'data' => $categorias,
        ]);
    }

    /**
     * Detalle de una imagen por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $imagen = $this->galeriaService->obtenerPorId($id);

        return response()->json([
            'data' => new CmsGaleriaResource($imagen),
        ]);
    }
}
