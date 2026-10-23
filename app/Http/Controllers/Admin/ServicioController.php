<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Services\CmsServiciosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServicioController extends Controller
{
    public function __construct(
        protected CmsServiciosService $serviciosService
    ) {}

    /**
     * Muestra el listado de servicios con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/Servicios/Index', [
            'servicios' => $this->serviciosService->obtenerTodas($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Muestra la vista detallada de un servicio.
     */
    public function show(int $id): Response
    {
        return Inertia::render('Admin/Servicios/Show', [
            'servicio' => $this->serviciosService->obtenerPorId($id),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo servicio.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Servicios/Create');
    }

    /**
     * Almacena un nuevo servicio y su archivo de imagen en la base de datos.
     */
    public function store(StoreServicioRequest $request): RedirectResponse
    {
        $this->serviciosService->guardar(
            $request->validated(),
            $request->file('sv_imagen')
        );

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un servicio existente.
     */
    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Servicios/Edit', [
            'servicio' => $this->serviciosService->obtenerPorId($id),
        ]);
    }

    /**
     * Actualiza la información de un servicio.
     */
    public function update(UpdateServicioRequest $request, int $id): RedirectResponse
    {
        $this->serviciosService->actualizar(
            $id,
            $request->validated(),
            $request->file('sv_imagen')
        );

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Elimina un servicio y sus archivos multimedia.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->serviciosService->eliminar($id);

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio eliminado correctamente.');
    }
}
