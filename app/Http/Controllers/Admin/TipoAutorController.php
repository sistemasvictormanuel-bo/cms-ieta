<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoAutorRequest;
use App\Http\Requests\UpdateTipoAutorRequest;
use App\Models\TipoAutor;
use App\Services\TipoAutorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoAutorController extends Controller
{
    public function __construct(
        protected TipoAutorService $tipoAutorService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/TipoAutores/Index', [
            'tipos' => $this->tipoAutorService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/TipoAutores/Create');
    }

    public function store(StoreTipoAutorRequest $request): RedirectResponse
    {
        $this->tipoAutorService->guardar($request->validated());

        return redirect()->route('admin.tipo-autores.index')
            ->with('success', 'Tipo de autor creado correctamente.');
    }

    public function show(int|TipoAutor $tipoAutore): Response
    {
        $id = $tipoAutore instanceof TipoAutor ? $tipoAutore->id : (int) $tipoAutore;

        return Inertia::render('Admin/TipoAutores/Show', [
            'tipo' => $this->tipoAutorService->obtenerPorId($id),
        ]);
    }

    public function edit(int|TipoAutor $tipoAutore): Response
    {
        $id = $tipoAutore instanceof TipoAutor ? $tipoAutore->id : (int) $tipoAutore;

        return Inertia::render('Admin/TipoAutores/Edit', [
            'tipo' => $this->tipoAutorService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateTipoAutorRequest $request, int|TipoAutor $tipoAutore): RedirectResponse
    {
        $id = $tipoAutore instanceof TipoAutor ? $tipoAutore->id : (int) $tipoAutore;
        $this->tipoAutorService->actualizar($id, $request->validated());

        return redirect()->route('admin.tipo-autores.index')
            ->with('success', 'Tipo de autor actualizado correctamente.');
    }

    public function destroy(int|TipoAutor $tipoAutore): RedirectResponse
    {
        $id = $tipoAutore instanceof TipoAutor ? $tipoAutore->id : (int) $tipoAutore;
        $this->tipoAutorService->eliminar($id);

        return redirect()->route('admin.tipo-autores.index')
            ->with('success', 'Tipo de autor eliminado correctamente.');
    }
}
