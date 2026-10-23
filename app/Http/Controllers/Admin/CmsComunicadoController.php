<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsComunicadoRequest;
use App\Http\Requests\UpdateCmsComunicadoRequest;
use App\Models\CmsComunicado;
use App\Services\CmsComunicadoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsComunicadoController extends Controller
{
    public function __construct(
        protected CmsComunicadoService $comunicadoService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $activo = $request->has('activo') && $request->input('activo') !== ''
            ? filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $tipo = $request->input('tipo');
        $perPage = (int) $request->input('per_page', 10);

        $stats = [
            'total' => CmsComunicado::count(),
            'activos' => CmsComunicado::where('com_activo', true)->count(),
            'pausados' => CmsComunicado::where('com_activo', false)->count(),
        ];

        return Inertia::render('Admin/Comunicados/Index', [
            'comunicados' => $this->comunicadoService->obtenerTodos($search, $activo, $tipo, $perPage),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'activo' => $request->input('activo'),
                'tipo' => $tipo,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Comunicados/Create');
    }

    public function store(StoreCmsComunicadoRequest $request): RedirectResponse
    {
        $this->comunicadoService->guardar(
            $request->validated(),
            $request->file('com_flyer')
        );

        return redirect()->route('admin.comunicados.index')
            ->with('success', 'Comunicado / Novedad creada exitosamente.');
    }

    public function show(int|CmsComunicado $comunicado): Response
    {
        $id = $comunicado instanceof CmsComunicado ? $comunicado->id : (int) $comunicado;

        return Inertia::render('Admin/Comunicados/Show', [
            'comunicado' => $this->comunicadoService->obtenerPorId($id),
        ]);
    }

    public function edit(int|CmsComunicado $comunicado): Response
    {
        $id = $comunicado instanceof CmsComunicado ? $comunicado->id : (int) $comunicado;

        return Inertia::render('Admin/Comunicados/Edit', [
            'comunicado' => $this->comunicadoService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateCmsComunicadoRequest $request, int|CmsComunicado $comunicado): RedirectResponse
    {
        $id = $comunicado instanceof CmsComunicado ? $comunicado->id : (int) $comunicado;

        $this->comunicadoService->actualizar(
            $id,
            $request->validated(),
            $request->file('com_flyer')
        );

        return redirect()->route('admin.comunicados.index')
            ->with('success', 'Comunicado / Novedad actualizada exitosamente.');
    }

    public function destroy(int|CmsComunicado $comunicado): RedirectResponse
    {
        $id = $comunicado instanceof CmsComunicado ? $comunicado->id : (int) $comunicado;
        $this->comunicadoService->eliminar($id);

        return redirect()->route('admin.comunicados.index')
            ->with('success', 'Comunicado eliminado exitosamente.');
    }

    public function toggleActivo(int $id): RedirectResponse
    {
        $comunicado = $this->comunicadoService->toggleActivo($id);
        $estado = $comunicado->com_activo ? 'activado' : 'pausado';

        return back()->with('success', "Comunicado {$estado} correctamente.");
    }
}
