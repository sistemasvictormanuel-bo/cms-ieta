<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonalResource;
use App\Services\PersonalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonalController extends Controller
{
    public function __construct(
        protected PersonalService $personalService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('search');
        $estado = $request->has('estado') && $request->input('estado') !== ''
            ? filter_var($request->input('estado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : true;
        $perPage = (int) $request->input('per_page', 15);

        $personal = $this->personalService->obtenerTodos($search, $estado, $perPage);

        return PersonalResource::collection($personal);
    }

    public function show(int $id): JsonResponse
    {
        $miembro = $this->personalService->obtenerPorId($id);

        return response()->json([
            'data' => new PersonalResource($miembro),
        ]);
    }
}
