<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsRevistasVariasRequest;
use App\Http\Requests\UpdateCmsRevistasVariasRequest;
use App\Models\CmsRevistasVarias;
use App\Services\CmsRevistasVariasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsRevistasVariasController extends Controller
{
    public function __construct(
        protected CmsRevistasVariasService $revistasService
    ) {}

    /**
     * Muestra el listado de revistas de referencia.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $activo = $request->has('activo') && $request->input('activo') !== ''
            ? filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $perPage = (int) $request->input('per_page', 12);

        $stats = [
            'total' => CmsRevistasVarias::count(),
            'activas' => CmsRevistasVarias::where('rev_activo', true)->count(),
            'inactivas' => CmsRevistasVarias::where('rev_activo', false)->count(),
        ];

        return Inertia::render('Admin/RevistasVarias/Index', [
            'revistas' => $this->revistasService->obtenerTodos($search, $activo, $perPage),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'activo' => $request->input('activo'),
                'per_page' => $perPage,
                'view' => $request->input('view', 'cards'),
            ],
        ]);
    }

    /**
     * Formulario de registro de una nueva revista de referencia.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/RevistasVarias/Create');
    }

    /**
     * Almacena una nueva revista de referencia.
     */
    public function store(StoreCmsRevistasVariasRequest $request): RedirectResponse
    {
        $this->revistasService->guardar(
            $request->validated(),
            $request->file('portada')
        );

        return redirect()->route('admin.revistas-varias.index')
            ->with('success', 'Revista de referencia registrada correctamente.');
    }

    /**
     * Muestra el detalle de una revista referencial.
     */
    public function show(int|CmsRevistasVarias $revistas_varium): Response
    {
        $id = $revistas_varium instanceof CmsRevistasVarias ? $revistas_varium->id : (int) $revistas_varium;

        return Inertia::render('Admin/RevistasVarias/Show', [
            'revista' => $this->revistasService->obtenerPorId($id),
        ]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(int|CmsRevistasVarias $revistas_varium): Response
    {
        $id = $revistas_varium instanceof CmsRevistasVarias ? $revistas_varium->id : (int) $revistas_varium;

        return Inertia::render('Admin/RevistasVarias/Edit', [
            'revista' => $this->revistasService->obtenerPorId($id),
        ]);
    }

    /**
     * Actualiza la información de la revista referencial.
     */
    public function update(UpdateCmsRevistasVariasRequest $request, int|CmsRevistasVarias $revistas_varium): RedirectResponse
    {
        $id = $revistas_varium instanceof CmsRevistasVarias ? $revistas_varium->id : (int) $revistas_varium;

        $this->revistasService->actualizar(
            $id,
            $request->validated(),
            $request->file('portada')
        );

        return redirect()->route('admin.revistas-varias.index')
            ->with('success', 'Revista de referencia actualizada exitosamente.');
    }

    /**
     * Elimina el registro de la revista.
     */
    public function destroy(int|CmsRevistasVarias $revistas_varium): RedirectResponse
    {
        $id = $revistas_varium instanceof CmsRevistasVarias ? $revistas_varium->id : (int) $revistas_varium;
        $this->revistasService->eliminar($id);

        return redirect()->route('admin.revistas-varias.index')
            ->with('success', 'Revista de referencia eliminada del sistema.');
    }

    /**
     * Alterna la visibilidad pública.
     */
    public function toggleActivo(int $id): RedirectResponse
    {
        $revista = $this->revistasService->toggleActivo($id);
        $estado = $revista->rev_activo ? 'activada en el portal' : 'ocultada del portal';

        return back()->with('success', "La revista ha sido {$estado}.");
    }
}
