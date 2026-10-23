<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsCursoRequest;
use App\Http\Requests\UpdateCmsCursoRequest;
use App\Models\CmsCursos;
use App\Models\Persona;
use App\Services\CursosServices;
use App\Services\ModalidadesCursos;
use App\Services\TipoCursosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsCursosController extends Controller
{
    public function __construct(
        protected CursosServices $cursosService,
        protected TipoCursosService $tipoCursosService,
        protected ModalidadesCursos $modalidadesService
    ) {}

    /**
     * Muestra el listado de cursos con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/Cursos/Index', [
            'cursos' => $this->cursosService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo curso.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Cursos/Create', [
            'tipos' => $this->tipoCursosService->obtenerListado(),
            'modalidades' => $this->modalidadesService->obtenerListado(),
            'docentes' => Persona::select('id', 'p_nombre', 'p_apellido_paterno', 'p_apellido_materno')
                ->orderBy('p_nombre')
                ->get(),
        ]);
    }

    /**
     * Almacena un nuevo curso en la base de datos junto con su portada.
     */
    public function store(StoreCmsCursoRequest $request): RedirectResponse
    {
        $this->cursosService->guardarCurso(
            $request->validated(),
            $request->file('portada_curso')
        );

        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso creado correctamente.');
    }

    /**
     * Muestra la vista detallada de un curso.
     */
    public function show(int|CmsCursos $cmsCursos): Response
    {
        $id = $cmsCursos instanceof CmsCursos ? $cmsCursos->id : (int) $cmsCursos;

        return Inertia::render('Admin/Cursos/Show', [
            'curso' => $this->cursosService->obtenerPorId($id),
        ]);
    }

    /**
     * Muestra el formulario para editar un curso existente.
     */
    public function edit(int|CmsCursos $cmsCursos): Response
    {
        $id = $cmsCursos instanceof CmsCursos ? $cmsCursos->id : (int) $cmsCursos;

        return Inertia::render('Admin/Cursos/Edit', [
            'curso' => $this->cursosService->obtenerPorId($id),
            'tipos' => $this->tipoCursosService->obtenerListado(),
            'modalidades' => $this->modalidadesService->obtenerListado(),
            'docentes' => Persona::select('id', 'p_nombre', 'p_apellido_paterno', 'p_apellido_materno')
                ->orderBy('p_nombre')
                ->get(),
        ]);
    }

    /**
     * Actualiza un curso existente y su portada.
     */
    public function update(UpdateCmsCursoRequest $request, int|CmsCursos $cmsCursos): RedirectResponse
    {
        $id = $cmsCursos instanceof CmsCursos ? $cmsCursos->id : (int) $cmsCursos;

        $this->cursosService->actualizarCurso(
            $id,
            $request->validated(),
            $request->file('portada_curso')
        );

        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso actualizado correctamente.');
    }

    /**
     * Elimina un curso y sus archivos multimedia.
     */
    public function destroy(int|CmsCursos $cmsCursos): RedirectResponse
    {
        $id = $cmsCursos instanceof CmsCursos ? $cmsCursos->id : (int) $cmsCursos;
        $this->cursosService->eliminarCurso($id);

        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso eliminado correctamente.');
    }
}
