<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsComiteEditorialRequest;
use App\Http\Requests\UpdateCmsComiteEditorialRequest;
use App\Models\CargoInstitucional;
use App\Models\CmsComiteEditorial;
use App\Models\Instituciones;
use App\Models\Persona;
use App\Services\CmsComiteEditorialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class CmsComiteEditorialController extends Controller
{
    public function __construct(
        protected CmsComiteEditorialService $comiteService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/ComiteEditorial/Index', [
            'miembros' => $this->comiteService->obtenerTodos($search, $tipo, $perPage),
            'filters' => [
                'search' => $search,
                'tipo' => $tipo,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ComiteEditorial/Create', [
            'personas' => Persona::orderBy('p_nombre')->get(),
            'cargos' => CargoInstitucional::orderBy('ci_nombre')->get(),
            'instituciones' => Instituciones::orderBy('inst_nombre')->get(),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function store(StoreCmsComiteEditorialRequest $request): RedirectResponse
    {
        $this->comiteService->guardar($request->validated());

        return redirect()->route('admin.comite-editorial.index')
            ->with('success', 'Miembro agregado al comité editorial correctamente.');
    }

    public function show(int|CmsComiteEditorial $comite_editorial): Response
    {
        $id = $comite_editorial instanceof CmsComiteEditorial ? $comite_editorial->id : (int) $comite_editorial;

        return Inertia::render('Admin/ComiteEditorial/Show', [
            'miembro' => $this->comiteService->obtenerPorId($id),
        ]);
    }

    public function edit(int|CmsComiteEditorial $comite_editorial): Response
    {
        $id = $comite_editorial instanceof CmsComiteEditorial ? $comite_editorial->id : (int) $comite_editorial;

        return Inertia::render('Admin/ComiteEditorial/Edit', [
            'miembro' => $this->comiteService->obtenerPorId($id),
            'personas' => Persona::orderBy('p_nombre')->get(),
            'cargos' => CargoInstitucional::orderBy('ci_nombre')->get(),
            'instituciones' => Instituciones::orderBy('inst_nombre')->get(),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function update(UpdateCmsComiteEditorialRequest $request, int|CmsComiteEditorial $comite_editorial): RedirectResponse
    {
        $id = $comite_editorial instanceof CmsComiteEditorial ? $comite_editorial->id : (int) $comite_editorial;
        $this->comiteService->actualizar($id, $request->validated());

        return redirect()->route('admin.comite-editorial.index')
            ->with('success', 'Miembro del comité editorial actualizado correctamente.');
    }

    public function destroy(int|CmsComiteEditorial $comite_editorial): RedirectResponse
    {
        $id = $comite_editorial instanceof CmsComiteEditorial ? $comite_editorial->id : (int) $comite_editorial;
        $this->comiteService->eliminar($id);

        return redirect()->route('admin.comite-editorial.index')
            ->with('success', 'Miembro eliminado del comité editorial.');
    }
}
