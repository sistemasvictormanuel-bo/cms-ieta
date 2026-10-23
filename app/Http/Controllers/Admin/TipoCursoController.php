<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoCursoRequest;
use App\Http\Requests\UpdateTipoCursoRequest;
use App\Models\TipoCurso;
use App\Services\TipoCursosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoCursoController extends Controller
{
    public function __construct(
        protected TipoCursosService $tipoCursosService
    ) {}

    /**
     * Muestra el listado de tipos de curso con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/TipoCursos/Index', [
            'tipoCursos' => $this->tipoCursosService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de curso.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/TipoCursos/Create');
    }

    /**
     * Almacena un nuevo tipo de curso.
     */
    public function store(StoreTipoCursoRequest $request): RedirectResponse
    {
        $this->tipoCursosService->guardar($request->validated());

        return redirect()->route('admin.tipo-cursos.index')
            ->with('success', 'Tipo de curso creado correctamente.');
    }

    /**
     * Muestra la vista detallada de un tipo de curso.
     */
    public function show(int|TipoCurso $tipoCurso): Response
    {
        $id = $tipoCurso instanceof TipoCurso ? $tipoCurso->id : (int) $tipoCurso;

        return Inertia::render('Admin/TipoCursos/Show', [
            'tipoCurso' => $this->tipoCursosService->obtenerPorId($id),
        ]);
    }

    /**
     * Muestra el formulario para editar un tipo de curso.
     */
    public function edit(int|TipoCurso $tipoCurso): Response
    {
        $id = $tipoCurso instanceof TipoCurso ? $tipoCurso->id : (int) $tipoCurso;

        return Inertia::render('Admin/TipoCursos/Edit', [
            'tipoCurso' => $this->tipoCursosService->obtenerPorId($id),
        ]);
    }

    /**
     * Actualiza un tipo de curso en la base de datos.
     */
    public function update(UpdateTipoCursoRequest $request, int|TipoCurso $tipoCurso): RedirectResponse
    {
        $id = $tipoCurso instanceof TipoCurso ? $tipoCurso->id : (int) $tipoCurso;
        $this->tipoCursosService->actualizar($id, $request->validated());

        return redirect()->route('admin.tipo-cursos.index')
            ->with('success', 'Tipo de curso actualizado correctamente.');
    }

    /**
     * Elimina un tipo de curso.
     */
    public function destroy(int|TipoCurso $tipoCurso): RedirectResponse
    {
        $id = $tipoCurso instanceof TipoCurso ? $tipoCurso->id : (int) $tipoCurso;
        $this->tipoCursosService->eliminar($id);

        return redirect()->route('admin.tipo-cursos.index')
            ->with('success', 'Tipo de curso eliminado correctamente.');
    }
}
