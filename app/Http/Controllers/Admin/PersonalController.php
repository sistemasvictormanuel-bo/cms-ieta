<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\CargoInstitucional;
use App\Models\Persona;
use App\Models\Personal;
use App\Services\PersonalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class PersonalController extends Controller
{
    public function __construct(
        protected PersonalService $personalService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $estado = $request->has('estado') && $request->input('estado') !== ''
            ? filter_var($request->input('estado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/Personal/Index', [
            'personal' => $this->personalService->obtenerTodos($search, $estado, $perPage),
            'filters' => [
                'search' => $search,
                'estado' => $request->input('estado'),
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Personal/Create', [
            'personas' => Persona::orderBy('p_nombre')->get(),
            'cargos' => CargoInstitucional::orderBy('ci_nombre')->get(),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function store(StorePersonalRequest $request): RedirectResponse
    {
        $this->personalService->guardar($request->validated());

        return redirect()->route('admin.personal.index')
            ->with('success', 'Miembro del personal registrado exitosamente.');
    }

    public function show(int|Personal $personal): Response
    {
        $id = $personal instanceof Personal ? $personal->id : (int) $personal;

        return Inertia::render('Admin/Personal/Show', [
            'miembro' => $this->personalService->obtenerPorId($id),
        ]);
    }

    public function edit(int|Personal $personal): Response
    {
        $id = $personal instanceof Personal ? $personal->id : (int) $personal;

        return Inertia::render('Admin/Personal/Edit', [
            'miembro' => $this->personalService->obtenerPorId($id),
            'personas' => Persona::orderBy('p_nombre')->get(),
            'cargos' => CargoInstitucional::orderBy('ci_nombre')->get(),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function update(UpdatePersonalRequest $request, int|Personal $personal): RedirectResponse
    {
        $id = $personal instanceof Personal ? $personal->id : (int) $personal;
        $this->personalService->actualizar($id, $request->validated());

        return redirect()->route('admin.personal.index')
            ->with('success', 'Información del personal actualizada exitosamente.');
    }

    public function destroy(int|Personal $personal): RedirectResponse
    {
        $id = $personal instanceof Personal ? $personal->id : (int) $personal;
        $this->personalService->eliminar($id);

        return redirect()->route('admin.personal.index')
            ->with('success', 'Miembro del personal dado de baja exitosamente.');
    }
}
