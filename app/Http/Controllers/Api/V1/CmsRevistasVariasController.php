<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsRevistasVariasResource;
use App\Services\CmsRevistasVariasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsRevistasVariasController extends Controller
{
    public function __construct(
        protected CmsRevistasVariasService $revistasService
    ) {}

    /**
     * Listado público de revistas de referencia activas.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 12);

        $revistas = $this->revistasService->obtenerTodos(
            $search,
            true, // solo activas
            $perPage
        );

        return CmsRevistasVariasResource::collection($revistas);
    }

    /**
     * Detalle de una revista de referencia.
     */
    public function show(int $id): JsonResponse
    {
        $revista = $this->revistasService->obtenerPorId($id);

        return response()->json([
            'data' => new CmsRevistasVariasResource($revista),
        ]);
    }
}
