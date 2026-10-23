<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstruccionAutorRequest;
use App\Http\Requests\UpdateInstruccionAutorRequest;
use App\Models\InstruccionAutor;
use App\Services\InstruccionAutorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstruccionAutorController extends Controller
{
    public function __construct(
        protected InstruccionAutorService $instruccionService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/InstruccionesAutores/Index', [
            'instrucciones' => $this->instruccionService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'total' => InstruccionAutor::count(),
        ]);
    }

    public function create(): Response
    {
        $proximoOrden = ((int) InstruccionAutor::max('ia_orden')) + 1;

        return Inertia::render('Admin/InstruccionesAutores/Create', [
            'siguienteOrden' => $proximoOrden,
        ]);
    }

    public function store(StoreInstruccionAutorRequest $request): RedirectResponse
    {
        $this->instruccionService->guardar($request->validated());

        return redirect()->route('admin.instrucciones-autores.index')
            ->with('success', 'Instrucción para autores registrada exitosamente.');
    }

    public function show(int|InstruccionAutor $instruccion_autore): Response
    {
        $id = $instruccion_autore instanceof InstruccionAutor ? $instruccion_autore->id : (int) $instruccion_autore;

        return Inertia::render('Admin/InstruccionesAutores/Show', [
            'instruccion' => $this->instruccionService->obtenerPorId($id),
        ]);
    }

    public function edit(int|InstruccionAutor $instruccion_autore): Response
    {
        $id = $instruccion_autore instanceof InstruccionAutor ? $instruccion_autore->id : (int) $instruccion_autore;

        return Inertia::render('Admin/InstruccionesAutores/Edit', [
            'instruccion' => $this->instruccionService->obtenerPorId($id),
        ]);
    }

    public function update(UpdateInstruccionAutorRequest $request, int|InstruccionAutor $instruccion_autore): RedirectResponse
    {
        $id = $instruccion_autore instanceof InstruccionAutor ? $instruccion_autore->id : (int) $instruccion_autore;
        $this->instruccionService->actualizar($id, $request->validated());

        return redirect()->route('admin.instrucciones-autores.index')
            ->with('success', 'Instrucción para autores actualizada exitosamente.');
    }

    public function destroy(int|InstruccionAutor $instruccion_autore): RedirectResponse
    {
        $id = $instruccion_autore instanceof InstruccionAutor ? $instruccion_autore->id : (int) $instruccion_autore;
        $this->instruccionService->eliminar($id);

        return redirect()->route('admin.instrucciones-autores.index')
            ->with('success', 'Instrucción eliminada exitosamente.');
    }

    public function toggleActivo(int $id): RedirectResponse
    {
        $instruccion = $this->instruccionService->toggleActivo($id);

        $estado = $instruccion->ia_activo ? 'activada' : 'desactivada';

        return back()->with('success', "La instrucción fue {$estado} correctamente.");
    }
}
