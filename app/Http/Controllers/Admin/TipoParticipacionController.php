<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoParticipacionRequest;
use App\Http\Requests\UpdateTipoParticipacionRequest;
use App\Models\TipoParticipacion;
use App\Services\TipoParticipacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoParticipacionController extends Controller
{
    public function __construct(
        protected TipoParticipacionService $tipoParticipacionService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/TipoParticipaciones/Index', [
            'tipos' => $this->tipoParticipacionService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/TipoParticipaciones/Create');
    }

    public function store(StoreTipoParticipacionRequest $request): RedirectResponse
    {
        $this->tipoParticipacionService->guardar($request->validated());

        return redirect()->route('admin.tipo-participaciones.index')
            ->with('success', 'Tipo de participación creado correctamente.');
    }

    public function show(int|TipoParticipacion $tipoParticipacione): Response
    {
        $id = $tipoParticipacione instanceof TipoParticipacion ? $tipoParticipacione->id : (int) $tipoParticipacione;

        return Inertia::render('Admin/TipoParticipaciones/Show', [
            'tipo' => $this->tipoParticipacionService->obtenerPorId($id),
        ]);
    }

    public function edit(int|TipoParticipacion $tipoParticipacione): Response
    {
        $id = $tipoParticipacione instanceof TipoParticipacion ? $tipoParticipacione->id : (int) $tipoParticipacione;

        return Inertia::render('Admin/TipoParticipaciones/Edit', [
            'tipo' => $this->tipoParticipacionService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateTipoParticipacionRequest $request, int|TipoParticipacion $tipoParticipacione): RedirectResponse
    {
        $id = $tipoParticipacione instanceof TipoParticipacion ? $tipoParticipacione->id : (int) $tipoParticipacione;
        $this->tipoParticipacionService->actualizar($id, $request->validated());

        return redirect()->route('admin.tipo-participaciones.index')
            ->with('success', 'Tipo de participación actualizado correctamente.');
    }

    public function destroy(int|TipoParticipacion $tipoParticipacione): RedirectResponse
    {
        $id = $tipoParticipacione instanceof TipoParticipacion ? $tipoParticipacione->id : (int) $tipoParticipacione;
        $this->tipoParticipacionService->eliminar($id);

        return redirect()->route('admin.tipo-participaciones.index')
            ->with('success', 'Tipo de participación eliminado correctamente.');
    }
}
