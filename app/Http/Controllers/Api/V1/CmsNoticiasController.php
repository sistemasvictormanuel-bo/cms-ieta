<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsNoticiasResource;
use App\Services\CmsNoticiasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsNoticiasController extends Controller
{
    public function __construct(
        protected CmsNoticiasService $noticiasService
    ) {}

    /**
     * Lista de noticias para la API pública con búsqueda y filtros.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $destacado = $request->has('destacado')
            ? filter_var($request->input('destacado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        // Por defecto en la API pública se muestran sólo noticias publicadas, salvo que se solicite explícitamente
        $publicado = $request->has('publicado')
            ? filter_var($request->input('publicado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : true;

        $personaId = $request->filled('persona_id') ? (int) $request->input('persona_id') : null;
        $perPage = (int) $request->input('per_page', 12);

        $noticias = $this->noticiasService->obtenerTodos($search, $publicado, $destacado, $personaId, $perPage);

        return CmsNoticiasResource::collection($noticias);
    }

    /**
     * Detalle de una noticia por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $noticia = $this->noticiasService->obtenerPorId($id);

        return response()->json([
            'data' => new CmsNoticiasResource($noticia),
        ]);
    }
}
