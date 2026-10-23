<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CmsLineaInvestigacionResource;
use App\Services\CmsLineasInvestigacionService;
use Illuminate\Http\Request;

class LineaInvestigacionController extends Controller
{
    public function __construct(protected CmsLineasInvestigacionService $lineas) {}

    /**
     * Retorna todos los Registros
     */
    public function lineasTodos()
    {
        $lineasInvestigacion = $this->lineas->obtenerTodas();

        if (! $lineasInvestigacion) {
            return response()->json(null, 200);
        }

        return CmsLineaInvestigacionResource::collection($lineasInvestigacion);
    }

    public function lineasPorId(string $id)
    {
        $linea = $this->lineas->obtenerPorId($id);

        if (! $linea) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        return new CmsLineaInvestigacionResource($linea); // aqui nuevo objeto si no encutra uno trata de cachear
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
