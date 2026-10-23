<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaTematicaRequest;
use App\Http\Requests\UpdateAreaTematicaRequest;
use App\Models\AreaTematicas;
use App\Services\AreaTematicasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AreaTematicasController extends Controller
{
    public function __construct(
        protected AreaTematicasService $areaTematicasService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/AreaTematicas/Index', [
            'areas' => $this->areaTematicasService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AreaTematicas/Create');
    }

    public function store(StoreAreaTematicaRequest $request): RedirectResponse
    {
        $this->areaTematicasService->guardar($request->validated());

        return redirect()->route('admin.area-tematicas.index')
            ->with('success', 'Área temática creada exitosamente.');
    }

    public function show(int|AreaTematicas $area_tematica): Response
    {
        $id = $area_tematica instanceof AreaTematicas ? $area_tematica->id : (int) $area_tematica;

        return Inertia::render('Admin/AreaTematicas/Show', [
            'area' => $this->areaTematicasService->obtenerPorId($id),
        ]);
    }

    public function edit(int|AreaTematicas $area_tematica): Response
    {
        $id = $area_tematica instanceof AreaTematicas ? $area_tematica->id : (int) $area_tematica;

        return Inertia::render('Admin/AreaTematicas/Edit', [
            'area' => $this->areaTematicasService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateAreaTematicaRequest $request, int|AreaTematicas $area_tematica): RedirectResponse
    {
        $id = $area_tematica instanceof AreaTematicas ? $area_tematica->id : (int) $area_tematica;
        $this->areaTematicasService->actualizar($id, $request->validated());

        return redirect()->route('admin.area-tematicas.index')
            ->with('success', 'Área temática actualizada exitosamente.');
    }

    public function destroy(int|AreaTematicas $area_tematica): RedirectResponse
    {
        $id = $area_tematica instanceof AreaTematicas ? $area_tematica->id : (int) $area_tematica;
        $this->areaTematicasService->eliminar($id);

        return redirect()->route('admin.area-tematicas.index')
            ->with('success', 'Área temática eliminada exitosamente.');
    }
}
