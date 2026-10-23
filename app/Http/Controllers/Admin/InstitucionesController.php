<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstitucionRequest;
use App\Http\Requests\UpdateInstitucionRequest;
use App\Models\Instituciones;
use App\Services\InstitucionesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstitucionesController extends Controller
{
    public function __construct(
        protected InstitucionesService $institucionesService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/Instituciones/Index', [
            'instituciones' => $this->institucionesService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Instituciones/Create');
    }

    public function store(StoreInstitucionRequest $request): RedirectResponse
    {
        $this->institucionesService->guardar($request->validated());

        return redirect()->route('admin.instituciones.index')
            ->with('success', 'Institución registrada exitosamente.');
    }

    public function show(int|Instituciones $institucione): Response
    {
        $id = $institucione instanceof Instituciones ? $institucione->id : (int) $institucione;

        return Inertia::render('Admin/Instituciones/Show', [
            'institucion' => $this->institucionesService->obtenerPorId($id),
        ]);
    }

    public function edit(int|Instituciones $institucione): Response
    {
        $id = $institucione instanceof Instituciones ? $institucione->id : (int) $institucione;

        return Inertia::render('Admin/Instituciones/Edit', [
            'institucion' => $this->institucionesService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateInstitucionRequest $request, int|Instituciones $institucione): RedirectResponse
    {
        $id = $institucione instanceof Instituciones ? $institucione->id : (int) $institucione;
        $this->institucionesService->actualizar($id, $request->validated());

        return redirect()->route('admin.instituciones.index')
            ->with('success', 'Institución actualizada exitosamente.');
    }

    public function destroy(int|Instituciones $institucione): RedirectResponse
    {
        $id = $institucione instanceof Instituciones ? $institucione->id : (int) $institucione;
        $this->institucionesService->eliminar($id);

        return redirect()->route('admin.instituciones.index')
            ->with('success', 'Institución eliminada exitosamente.');
    }
}
