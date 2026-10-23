<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEspecialidadRequest;
use App\Http\Requests\UpdateEspecialidadRequest;
use App\Models\Especialidades;
use App\Services\EspecialidadesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EspecialidadesController extends Controller
{
    public function __construct(
        protected EspecialidadesService $especialidadesService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/Especialidades/Index', [
            'especialidades' => $this->especialidadesService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Especialidades/Create');
    }

    public function store(StoreEspecialidadRequest $request): RedirectResponse
    {
        $this->especialidadesService->guardar($request->validated());

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    public function show(int|Especialidades $especialidade): Response
    {
        $id = $especialidade instanceof Especialidades ? $especialidade->id : (int) $especialidade;

        return Inertia::render('Admin/Especialidades/Show', [
            'especialidad' => $this->especialidadesService->obtenerPorId($id),
        ]);
    }

    public function edit(int|Especialidades $especialidade): Response
    {
        $id = $especialidade instanceof Especialidades ? $especialidade->id : (int) $especialidade;

        return Inertia::render('Admin/Especialidades/Edit', [
            'especialidad' => $this->especialidadesService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateEspecialidadRequest $request, int|Especialidades $especialidade): RedirectResponse
    {
        $id = $especialidade instanceof Especialidades ? $especialidade->id : (int) $especialidade;
        $this->especialidadesService->actualizar($id, $request->validated());

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(int|Especialidades $especialidade): RedirectResponse
    {
        $id = $especialidade instanceof Especialidades ? $especialidade->id : (int) $especialidade;
        $this->especialidadesService->eliminar($id);

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad eliminada correctamente.');
    }
}
