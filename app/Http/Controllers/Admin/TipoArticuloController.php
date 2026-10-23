<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoArticuloRequest;
use App\Http\Requests\UpdateTipoArticuloRequest;
use App\Services\TipoArticuloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TipoArticuloController extends Controller
{
    public function __construct(
        protected TipoArticuloService $tipoService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/TipoArticulos/Index', [
            'tipos' => $this->tipoService->obtenerTodos(),
        ]);
    }

    public function store(StoreTipoArticuloRequest $request): RedirectResponse
    {
        $this->tipoService->guardar($request->validated());

        return redirect()->route('admin.tipo-articulos.index')
            ->with('success', 'Tipo de artículo creado exitosamente.');
    }

    public function update(UpdateTipoArticuloRequest $request, int $id): RedirectResponse
    {
        $this->tipoService->actualizar($id, $request->validated());

        return redirect()->route('admin.tipo-articulos.index')
            ->with('success', 'Tipo de artículo actualizado exitosamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->tipoService->eliminar($id);

        return redirect()->route('admin.tipo-articulos.index')
            ->with('success', 'Tipo de artículo eliminado exitosamente.');
    }

    /**
     * Creación rápida desde selects de formularios en Artículos.
     */
    public function storeRapida(StoreTipoArticuloRequest $request): JsonResponse
    {
        $tipo = $this->tipoService->guardar($request->validated());

        return response()->json([
            'id' => $tipo->id,
            'tar_nombre' => $tipo->tar_nombre,
            'message' => 'Tipo de artículo creado exitosamente',
        ], 201);
    }
}
