<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstitucionResource;
use App\Services\InstitucionesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InstitucionController extends Controller
{
    public function __construct(
        protected InstitucionesService $institucionesService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $instituciones = $this->institucionesService->obtenerTodos($search, $perPage);

        return InstitucionResource::collection($instituciones);
    }

    public function show(int $id): JsonResponse
    {
        $institucion = $this->institucionesService->obtenerPorId($id);

        return response()->json([
            'data' => new InstitucionResource($institucion),
        ]);
    }
}
