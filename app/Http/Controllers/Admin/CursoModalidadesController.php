<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCursoModalidadRequest;
use App\Http\Requests\UpdateCursoModalidadRequest;
use App\Models\CursoModalidades;
use App\Services\ModalidadesCursos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CursoModalidadesController extends Controller
{
    public function __construct(
        protected ModalidadesCursos $modalidadService
    ) {}

    /**
     * Muestra el listado de modalidades de cursos con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/CursoModalidades/Index', [
            'modalidades' => $this->modalidadService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva modalidad.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/CursoModalidades/Create');
    }

    /**
     * Almacena una nueva modalidad de curso.
     */
    public function store(StoreCursoModalidadRequest $request): RedirectResponse
    {
        $this->modalidadService->guardar($request->validated());

        return redirect()->route('admin.curso-modalidades.index')
            ->with('success', 'Modalidad de curso creada correctamente.');
    }

    /**
     * Muestra el detalle de una modalidad.
     */
    public function show(int|CursoModalidades $cursoModalidades): Response
    {
        $id = $cursoModalidades instanceof CursoModalidades ? $cursoModalidades->id : (int) $cursoModalidades;

        return Inertia::render('Admin/CursoModalidades/Show', [
            'modalidad' => $this->modalidadService->obtenerPorId($id),
        ]);
    }

    /**
     * Muestra el formulario para editar una modalidad existente.
     */
    public function edit(int|CursoModalidades $cursoModalidades): Response
    {
        $id = $cursoModalidades instanceof CursoModalidades ? $cursoModalidades->id : (int) $cursoModalidades;

        return Inertia::render('Admin/CursoModalidades/Edit', [
            'modalidad' => $this->modalidadService->obtenerPorId($id),
        ]);
    }

    /**
     * Actualiza una modalidad de curso en la base de datos.
     */
    public function update(UpdateCursoModalidadRequest $request, int|CursoModalidades $cursoModalidades): RedirectResponse
    {
        $id = $cursoModalidades instanceof CursoModalidades ? $cursoModalidades->id : (int) $cursoModalidades;
        $this->modalidadService->actualizar($id, $request->validated());

        return redirect()->route('admin.curso-modalidades.index')
            ->with('success', 'Modalidad de curso actualizada correctamente.');
    }

    /**
     * Elimina una modalidad de curso.
     */
    public function destroy(int|CursoModalidades $cursoModalidades): RedirectResponse
    {
        $id = $cursoModalidades instanceof CursoModalidades ? $cursoModalidades->id : (int) $cursoModalidades;
        $this->modalidadService->eliminar($id);

        return redirect()->route('admin.curso-modalidades.index')
            ->with('success', 'Modalidad de curso eliminada correctamente.');
    }
}
