<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EdicionVarianzaResource;
use App\Services\EdicionVarianzaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class EdicionVarianzaController extends Controller
{
    public function __construct(
        protected EdicionVarianzaService $edicionService
    ) {}

    /**
     * Listado paginado de ediciones de la Revista Varianza.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $anio = $request->filled('anio') ? (int) $request->query('anio') : null;
        $perPage = (int) $request->query('per_page', 10);

        $ediciones = $this->edicionService->obtenerTodos($search, $anio, $perPage);

        return EdicionVarianzaResource::collection($ediciones);
    }

    /**
     * Retorna la edición más reciente o actual de la Revista Varianza.
     */
    public function actual(): EdicionVarianzaResource|JsonResponse
    {
        $edicion = $this->edicionService->obtenerUltimaEdicion(soloAprobados: true);

        if (! $edicion) {
            return response()->json(['message' => 'No existen ediciones publicadas disponibles.'], 404);
        }

        return new EdicionVarianzaResource($edicion);
    }

    /**
     * Detalle de una edición específica por ID o slug.
     */
    public function show(string|int $idOrSlug): EdicionVarianzaResource|JsonResponse
    {
        try {
            $edicion = is_numeric($idOrSlug)
                ? $this->edicionService->obtenerPorId((int) $idOrSlug, soloAprobados: true)
                : $this->edicionService->obtenerPorSlug((string) $idOrSlug, soloAprobados: true);

            return new EdicionVarianzaResource($edicion);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Edición no encontrada.'], 404);
        }
    }
}
