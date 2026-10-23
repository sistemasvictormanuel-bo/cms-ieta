<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsCursoResource;
use App\Models\CmsCursos;
use App\Services\CursosServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsCursoController extends Controller
{
    public function __construct(
        protected CursosServices $cursosService
    ) {}

    /**
     * Retorna el listado de cursos (con filtros opcionales de búsqueda y paginación).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 20);

        $cursos = $this->cursosService->obtenerTodos($search, $perPage);

        return CmsCursoResource::collection($cursos);
    }

    /**
     * Retorna el detalle de un curso específico por su ID o por su slug.
     */
    public function show(string $idOrSlug): CmsCursoResource|JsonResponse
    {
        try {
            if (is_numeric($idOrSlug)) {
                $curso = $this->cursosService->obtenerPorId((int) $idOrSlug);
            } else {
                $curso = CmsCursos::with(['media', 'tipoCurso', 'modalidadCurso', 'docente'])
                    ->where('ct_slug', $idOrSlug)
                    ->firstOrFail();
            }

            return new CmsCursoResource($curso);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
    }
}
