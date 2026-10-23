<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCargoInstitucionalRequest;
use App\Http\Requests\UpdateCargoInstitucionalRequest;
use App\Models\CargoInstitucional;
use App\Services\CargoInstitucionalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CargoInstitucionalController extends Controller
{
    public function __construct(
        protected CargoInstitucionalService $cargoService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/CargosInstitucionales/Index', [
            'cargos' => $this->cargoService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CargosInstitucionales/Create');
    }

    public function store(StoreCargoInstitucionalRequest $request): RedirectResponse
    {
        $this->cargoService->guardar($request->validated());

        return redirect()->route('admin.cargos-institucionales.index')
            ->with('success', 'Cargo institucional creado exitosamente.');
    }

    public function show(int|CargoInstitucional $cargos_institucionale): Response
    {
        $id = $cargos_institucionale instanceof CargoInstitucional ? $cargos_institucionale->id : (int) $cargos_institucionale;

        return Inertia::render('Admin/CargosInstitucionales/Show', [
            'cargo' => $this->cargoService->obtenerPorId($id),
        ]);
    }

    public function edit(int|CargoInstitucional $cargos_institucionale): Response
    {
        $id = $cargos_institucionale instanceof CargoInstitucional ? $cargos_institucionale->id : (int) $cargos_institucionale;

        return Inertia::render('Admin/CargosInstitucionales/Edit', [
            'cargo' => $this->cargoService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateCargoInstitucionalRequest $request, int|CargoInstitucional $cargos_institucionale): RedirectResponse
    {
        $id = $cargos_institucionale instanceof CargoInstitucional ? $cargos_institucionale->id : (int) $cargos_institucionale;
        $this->cargoService->actualizar($id, $request->validated());

        return redirect()->route('admin.cargos-institucionales.index')
            ->with('success', 'Cargo institucional actualizado exitosamente.');
    }

    public function destroy(int|CargoInstitucional $cargos_institucionale): RedirectResponse
    {
        $id = $cargos_institucionale instanceof CargoInstitucional ? $cargos_institucionale->id : (int) $cargos_institucionale;
        $this->cargoService->eliminar($id);

        return redirect()->route('admin.cargos-institucionales.index')
            ->with('success', 'Cargo institucional eliminado exitosamente.');
    }
}
