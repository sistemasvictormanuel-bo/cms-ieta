<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AutoresExport;
use App\Exports\AutoresPlantillaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAutorRequest;
use App\Http\Requests\UpdateAutorRequest;
use App\Imports\AutoresImport;
use App\Models\Autor;
use App\Models\Especialidades;
use App\Models\GradoAcademico;
use App\Models\Persona;
use App\Models\TipoAutor;
use App\Services\AutorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AutorController extends Controller
{
    public function __construct(
        protected AutorService $autorService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/Autores/Index', [
            'autores' => $this->autorService->obtenerTodos($search, $perPage),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Autores/Create', [
            'personas' => Persona::orderBy('p_nombre')->get(),
            'tipos' => TipoAutor::where('ta_estado', true)->orderBy('ta_nombre')->get(),
            'especialidades' => Especialidades::where('ep_estado', true)->orderBy('ep_nombre')->get(),
            'grados' => GradoAcademico::orderBy('ga_nombre')->get(),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function store(StoreAutorRequest $request): RedirectResponse
    {
        $this->autorService->guardar($request->validated());

        return redirect()->route('admin.autores.index')
            ->with('success', 'Autor registrado correctamente.');
    }

    public function show(int|Autor $autore): Response
    {
        $id = $autore instanceof Autor ? $autore->id : (int) $autore;

        return Inertia::render('Admin/Autores/Show', [
            'autor' => $this->autorService->obtenerPorId($id),
        ]);
    }

    public function edit(int|Autor $autore): Response
    {
        $id = $autore instanceof Autor ? $autore->id : (int) $autore;

        return Inertia::render('Admin/Autores/Edit', [
            'autor' => $this->autorService->obtenerPorId($id),
            'personas' => Persona::orderBy('p_nombre')->get(),
            'tipos' => TipoAutor::where('ta_estado', true)->orderBy('ta_nombre')->get(),
            'especialidades' => Especialidades::where('ep_estado', true)->orderBy('ep_nombre')->get(),
            'grados' => GradoAcademico::orderBy('ga_nombre')->get(),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function update(UpdateAutorRequest $request, int|Autor $autore): RedirectResponse
    {
        $id = $autore instanceof Autor ? $autore->id : (int) $autore;
        $this->autorService->actualizar($id, $request->validated());

        return redirect()->route('admin.autores.index')
            ->with('success', 'Autor actualizado correctamente.');
    }

    public function destroy(int|Autor $autore): RedirectResponse
    {
        $id = $autore instanceof Autor ? $autore->id : (int) $autore;
        $this->autorService->eliminar($id);

        return redirect()->route('admin.autores.index')
            ->with('success', 'Autor eliminado correctamente.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return Excel::download(new AutoresExport($request), 'autores_'.date('Y-m-d').'.xlsx');
    }

    public function plantilla(): BinaryFileResponse
    {
        return Excel::download(new AutoresPlantillaExport, 'plantilla_autores.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo_excel' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel o CSV.',
            'archivo_excel.mimes' => 'El archivo debe tener formato .xlsx, .xls o .csv.',
            'archivo_excel.max' => 'El archivo no debe exceder los 10MB.',
        ]);

        $import = new AutoresImport;
        Excel::import($import, $request->file('archivo_excel'));

        $importados = $import->getImportedCount();
        $omitidos = $import->getSkippedCount();
        $errores = $import->getErrors();

        if ($importados > 0 && empty($errores)) {
            return redirect()->route('admin.autores.index')
                ->with('success', "Se importaron exitosamente {$importados} autor(es)/investigador(es).");
        }

        if ($importados > 0 && ! empty($errores)) {
            return redirect()->route('admin.autores.index')
                ->with('warning', "Se importaron {$importados} autor(es), pero hubo {$omitidos} fila(s) con observaciones.")
                ->with('import_errors', $errores);
        }

        return redirect()->route('admin.autores.index')
            ->with('error', 'No se pudo importar ningún autor. Se detectaron observaciones en el archivo.')
            ->with('import_errors', $errores);
    }
}
