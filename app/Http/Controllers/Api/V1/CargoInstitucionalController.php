<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CargoInstitucionalResource;
use App\Services\CargoInstitucionalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CargoInstitucionalController extends Controller
{
    public function __construct(
        protected CargoInstitucionalService $cargoService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $cargos = $this->cargoService->obtenerTodos($search, $perPage);

        return CargoInstitucionalResource::collection($cargos);
    }

    public function show(int $id): JsonResponse
    {
        $cargo = $this->cargoService->obtenerPorId($id);

        return response()->json([
            'data' => new CargoInstitucionalResource($cargo),
        ]);
    }
}
