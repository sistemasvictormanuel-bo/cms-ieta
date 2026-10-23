<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsComiteEditorialResource;
use App\Services\CmsComiteEditorialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsComiteEditorialController extends Controller
{
    public function __construct(
        protected CmsComiteEditorialService $comiteService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');
        $perPage = (int) $request->input('per_page', 15);

        $miembros = $this->comiteService->obtenerTodos($search, $tipo, $perPage);

        return CmsComiteEditorialResource::collection($miembros);
    }

    public function show(int $id): JsonResponse
    {
        $miembro = $this->comiteService->obtenerPorId($id);

        return response()->json([
            'data' => new CmsComiteEditorialResource($miembro),
        ]);
    }
}
