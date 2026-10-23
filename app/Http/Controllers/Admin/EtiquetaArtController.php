<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEtiquetaArtRequest;
use App\Http\Requests\UpdateEtiquetaArtRequest;
use App\Models\EtiquetaArt;
use App\Services\EtiquetaArtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EtiquetaArtController extends Controller
{
    public function __construct(
        protected EtiquetaArtService $etiquetaService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/EtiquetasArt/Index', [
            'etiquetas' => $this->etiquetaService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'total' => EtiquetaArt::count(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/EtiquetasArt/Create');
    }

    public function store(StoreEtiquetaArtRequest $request): RedirectResponse
    {
        $this->etiquetaService->guardar($request->validated());

        return redirect()->route('admin.etiquetas-art.index')
            ->with('success', 'Etiqueta creada exitosamente.');
    }

    public function edit(int|EtiquetaArt $etiqueta): Response
    {
        $id = $etiqueta instanceof EtiquetaArt ? $etiqueta->id : (int) $etiqueta;

        return Inertia::render('Admin/EtiquetasArt/Edit', [
            'etiqueta' => $this->etiquetaService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateEtiquetaArtRequest $request, int|EtiquetaArt $etiqueta): RedirectResponse
    {
        $id = $etiqueta instanceof EtiquetaArt ? $etiqueta->id : (int) $etiqueta;

        $this->etiquetaService->actualizar($id, $request->validated());

        return redirect()->route('admin.etiquetas-art.index')
            ->with('success', 'Etiqueta actualizada exitosamente.');
    }

    public function destroy(int|EtiquetaArt $etiqueta): RedirectResponse
    {
        $id = $etiqueta instanceof EtiquetaArt ? $etiqueta->id : (int) $etiqueta;

        $this->etiquetaService->eliminar($id);

        return redirect()->route('admin.etiquetas-art.index')
            ->with('success', 'Etiqueta eliminada exitosamente.');
    }

    /**
     * Endpoint para creación rápida de etiquetas directamente desde el modal o select del formulario de artículo.
     */
    public function storeRapida(StoreEtiquetaArtRequest $request): JsonResponse
    {
        $etiqueta = $this->etiquetaService->guardar($request->validated());

        return response()->json([
            'id' => $etiqueta->id,
            'ea_nombre' => $etiqueta->ea_nombre,
            'ea_slug' => $etiqueta->ea_slug,
            'message' => 'Etiqueta creada con éxito',
        ], 201);
    }
}
