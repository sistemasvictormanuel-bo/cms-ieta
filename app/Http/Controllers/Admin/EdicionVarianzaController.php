<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEdicionVarianzaRequest;
use App\Http\Requests\UpdateEdicionVarianzaRequest;
use App\Models\EdicionVarianza;
use App\Services\EdicionVarianzaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EdicionVarianzaController extends Controller
{
    public function __construct(
        protected EdicionVarianzaService $edicionService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $anio = $request->filled('anio') ? (int) $request->input('anio') : null;
        $perPage = (int) $request->input('per_page', 10);

        $aniosDisponibles = EdicionVarianza::select('ev_anio')
            ->distinct()
            ->orderByDesc('ev_anio')
            ->pluck('ev_anio');

        return Inertia::render('Admin/EdicionesVarianza/Index', [
            'ediciones' => $this->edicionService->obtenerTodos($search, $anio, $perPage),
            'anios' => $aniosDisponibles,
            'filters' => [
                'search' => $search,
                'anio' => $anio,
                'per_page' => $perPage,
            ],
            'stats' => [
                'total' => EdicionVarianza::count(),
                'publicadas' => EdicionVarianza::where('ev_estado', true)->count(),
                'borradores' => EdicionVarianza::where('ev_estado', false)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/EdicionesVarianza/Create');
    }

    public function store(StoreEdicionVarianzaRequest $request): RedirectResponse
    {
        $edicion = $this->edicionService->guardar(
            $request->validated(),
            $request->file('ev_portada'),
            $request->file('ev_archivo')
        );

        return redirect()->route('admin.ediciones-varianza.show', $edicion->id)
            ->with('success', 'Edición de Revista Varianza registrada exitosamente.');
    }

    public function show(int|EdicionVarianza $edicion): Response
    {
        $id = $edicion instanceof EdicionVarianza ? $edicion->id : (int) $edicion;

        return Inertia::render('Admin/EdicionesVarianza/Show', [
            'edicion' => $this->edicionService->obtenerPorId($id),
        ]);
    }

    public function edit(int|EdicionVarianza $edicion): Response
    {
        $id = $edicion instanceof EdicionVarianza ? $edicion->id : (int) $edicion;

        return Inertia::render('Admin/EdicionesVarianza/Edit', [
            'edicion' => $this->edicionService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateEdicionVarianzaRequest $request, int|EdicionVarianza $edicion): RedirectResponse
    {
        $id = $edicion instanceof EdicionVarianza ? $edicion->id : (int) $edicion;

        $this->edicionService->actualizar(
            $id,
            $request->validated(),
            $request->file('ev_portada'),
            $request->file('ev_archivo')
        );

        return redirect()->route('admin.ediciones-varianza.show', $id)
            ->with('success', 'Edición de Revista Varianza actualizada exitosamente.');
    }

    public function destroy(int|EdicionVarianza $edicion): RedirectResponse
    {
        $id = $edicion instanceof EdicionVarianza ? $edicion->id : (int) $edicion;

        $this->edicionService->eliminar($id);

        return redirect()->route('admin.ediciones-varianza.index')
            ->with('success', 'Edición eliminada exitosamente.');
    }
}
