<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradoAcademicoRequest;
use App\Http\Requests\UpdateGradoAcademicoRequest;
use App\Models\GradoAcademico;
use App\Services\GradoAcademicoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradoAcademicoController extends Controller
{
    public function __construct(
        protected GradoAcademicoService $gradoAcademicoService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/GradosAcademicos/Index', [
            'grados' => $this->gradoAcademicoService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/GradosAcademicos/Create');
    }

    public function store(StoreGradoAcademicoRequest $request): RedirectResponse
    {
        $this->gradoAcademicoService->guardar($request->validated());

        return redirect()->route('admin.grado-academicos.index')
            ->with('success', 'Grado académico creado correctamente.');
    }

    public function show(int|GradoAcademico $gradoAcademico): Response
    {
        $id = $gradoAcademico instanceof GradoAcademico ? $gradoAcademico->id : (int) $gradoAcademico;

        return Inertia::render('Admin/GradosAcademicos/Show', [
            'grado' => $this->gradoAcademicoService->obtenerPorId($id),
        ]);
    }

    public function edit(int|GradoAcademico $gradoAcademico): Response
    {
        $id = $gradoAcademico instanceof GradoAcademico ? $gradoAcademico->id : (int) $gradoAcademico;

        return Inertia::render('Admin/GradosAcademicos/Edit', [
            'grado' => $this->gradoAcademicoService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateGradoAcademicoRequest $request, int|GradoAcademico $gradoAcademico): RedirectResponse
    {
        $id = $gradoAcademico instanceof GradoAcademico ? $gradoAcademico->id : (int) $gradoAcademico;
        $this->gradoAcademicoService->actualizar($id, $request->validated());

        return redirect()->route('admin.grado-academicos.index')
            ->with('success', 'Grado académico actualizado correctamente.');
    }

    public function destroy(int|GradoAcademico $gradoAcademico): RedirectResponse
    {
        $id = $gradoAcademico instanceof GradoAcademico ? $gradoAcademico->id : (int) $gradoAcademico;
        $this->gradoAcademicoService->eliminar($id);

        return redirect()->route('admin.grado-academicos.index')
            ->with('success', 'Grado académico eliminado correctamente.');
    }
}
