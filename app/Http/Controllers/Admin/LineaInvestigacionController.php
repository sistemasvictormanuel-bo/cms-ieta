<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLineaInvestigacion;
use App\Http\Requests\UpdateLineaInvestigacion;
use App\Services\CmsLineasInvestigacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LineaInvestigacionController extends Controller
{
    public function __construct(
        protected CmsLineasInvestigacionService $lineasInvestigacionService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/LineasInvestigacion/Index', [
            'lineas' => $this->lineasInvestigacionService->obtenerTodas($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(int|string $lineas_investigacion): Response
    {
        $linea = is_numeric($lineas_investigacion)
            ? $this->lineasInvestigacionService->obtenerPorId((int) $lineas_investigacion)
            : $this->lineasInvestigacionService->obtenerPorSlug((string) $lineas_investigacion);

        return Inertia::render('Admin/LineasInvestigacion/Show', [
            'linea' => $linea,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/LineasInvestigacion/Create');
    }

    public function store(StoreLineaInvestigacion $request): RedirectResponse
    {
        $this->lineasInvestigacionService->guardar(
            $request->validated(),
            $request->file('li_imagen')
        );

        return redirect()->route('admin.lineas-investigacion.index')
            ->with('success', 'Línea de investigación creada correctamente.');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/LineasInvestigacion/Edit', [
            'linea' => $this->lineasInvestigacionService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateLineaInvestigacion $request, int $id): RedirectResponse
    {
        $this->lineasInvestigacionService->actualizar(
            $id,
            $request->validated(),
            $request->file('li_imagen')
        );

        return redirect()->route('admin.lineas-investigacion.index')
            ->with('success', 'Línea de investigación actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->lineasInvestigacionService->eliminar($id);

        return redirect()->route('admin.lineas-investigacion.index')
            ->with('success', 'Línea de investigación eliminada correctamente.');
    }
}
