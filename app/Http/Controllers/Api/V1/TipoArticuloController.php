<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TipoArticuloResource;
use App\Services\TipoArticuloService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TipoArticuloController extends Controller
{
    public function __construct(
        protected TipoArticuloService $tipoArticuloService
    ) {}

    /**
     * Listado de tipos de artículos disponibles (ej: Artículo Original, Nota Científica).
     */
    public function index(): AnonymousResourceCollection
    {
        $tipos = $this->tipoArticuloService->obtenerTodos();

        return TipoArticuloResource::collection($tipos);
    }
}
