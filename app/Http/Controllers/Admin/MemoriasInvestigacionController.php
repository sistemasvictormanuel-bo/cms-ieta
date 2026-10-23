<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemoriasInvestigacionRequest;
use App\Http\Requests\UpdateMemoriasInvestigacionRequest;
use App\Models\Autor;
use App\Models\MemoriasInvestigacion;
use App\Services\MemoriasInvestigacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemoriasInvestigacionController extends Controller
{
    public function __construct(
        protected MemoriasInvestigacionService $memoriasService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $autorId = $request->filled('autor_id') ? (int) $request->input('autor_id') : null;
        $etiqueta = $request->input('etiqueta');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/MemoriasInvestigacion/Index', [
            'memorias' => $this->memoriasService->obtenerTodos($search, $autorId, $etiqueta, $perPage),
            'autores' => Autor::with('persona')->get(),
            'filters' => [
                'search' => $search,
                'autor_id' => $autorId,
                'etiqueta' => $etiqueta,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/MemoriasInvestigacion/Create', [
            'autores' => Autor::with(['persona', 'tipoAutor'])->get(),
        ]);
    }

    public function store(StoreMemoriasInvestigacionRequest $request): RedirectResponse
    {
        $this->memoriasService->guardar(
            $request->validated(),
            $request->file('mdi_archivo')
        );

        return redirect()->route('admin.memorias-investigacion.index')
            ->with('success', 'Memoria de investigación registrada exitosamente.');
    }

    public function show(int|MemoriasInvestigacion $memorias_investigacion): Response
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;

        return Inertia::render('Admin/MemoriasInvestigacion/Show', [
            'memoria' => $this->memoriasService->obtenerPorId($id),
        ]);
    }

    public function edit(int|MemoriasInvestigacion $memorias_investigacion): Response
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;

        return Inertia::render('Admin/MemoriasInvestigacion/Edit', [
            'memoria' => $this->memoriasService->obtenerPorId($id),
            'autores' => Autor::with(['persona', 'tipoAutor'])->get(),
        ]);
    }

    public function update(UpdateMemoriasInvestigacionRequest $request, int|MemoriasInvestigacion $memorias_investigacion): RedirectResponse
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;

        $this->memoriasService->actualizar(
            $id,
            $request->validated(),
            $request->file('mdi_archivo')
        );

        return redirect()->route('admin.memorias-investigacion.index')
            ->with('success', 'Memoria de investigación actualizada exitosamente.');
    }

    public function destroy(int|MemoriasInvestigacion $memorias_investigacion): RedirectResponse
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;
        $this->memoriasService->eliminar($id);

        return redirect()->route('admin.memorias-investigacion.index')
            ->with('success', 'Memoria de investigación eliminada exitosamente.');
    }
}
