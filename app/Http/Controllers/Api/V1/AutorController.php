<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AutorResource;
use App\Services\AutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AutorController extends Controller
{
    public function __construct(
        protected AutorService $autorService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 20);

        $autores = $this->autorService->obtenerTodos($search, $perPage);

        return AutorResource::collection($autores);
    }

    public function show(string $idOrSlug): AutorResource|JsonResponse
    {
        try {
            if (is_numeric($idOrSlug)) {
                $autor = $this->autorService->obtenerPorId((int) $idOrSlug, soloAprobados: true);
            } else {
                $autor = $this->autorService->obtenerPorSlug($idOrSlug, soloAprobados: true);
            }

            return new AutorResource($autor);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Autor no encontrado'], 404);
        }
    }
}
