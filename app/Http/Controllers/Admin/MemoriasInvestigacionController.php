<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MemoriasInvestigacionExport;
use App\Exports\MemoriasInvestigacionPlantillaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemoriasInvestigacionRequest;
use App\Http\Requests\UpdateMemoriasInvestigacionRequest;
use App\Imports\MemoriasInvestigacionImport;
use App\Models\Autor;
use App\Models\MemoriasInvestigacion;
use App\Services\MemoriasInvestigacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemoriasInvestigacionController extends Controller
{
    public function __construct(
        protected MemoriasInvestigacionService $memoriasService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $autorId = $request->filled('autor_id') ? (int) $request->input('autor_id') : null;
        $etiqueta = $request->input('etiqueta');
        $perPage = min(max((int) $request->input('per_page', 10), 5), 250);

        return Inertia::render('Admin/MemoriasInvestigacion/Index', [
            'memorias' => $this->memoriasService->obtenerTodos($search, $autorId, $etiqueta, $perPage),
            'autores' => Autor::with('persona')->get(),
            'filters' => [
                'search' => $search,
                'autor_id' => $autorId,
                'etiqueta' => $etiqueta,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/MemoriasInvestigacion/Create', [
            'autores' => Autor::with(['persona', 'tipoAutor'])->get(),
        ]);
    }

    public function store(StoreMemoriasInvestigacionRequest $request): RedirectResponse
    {
        $this->memoriasService->guardar(
            $request->validated(),
            $request->file('mdi_archivo')
        );

        return redirect()->route('admin.memorias-investigacion.index')
            ->with('success', 'Memoria de investigación registrada exitosamente.');
    }

    public function show(int|MemoriasInvestigacion $memorias_investigacion): Response
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;

        return Inertia::render('Admin/MemoriasInvestigacion/Show', [
            'memoria' => $this->memoriasService->obtenerPorId($id),
        ]);
    }

    public function edit(int|MemoriasInvestigacion $memorias_investigacion): Response
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;

        return Inertia::render('Admin/MemoriasInvestigacion/Edit', [
            'memoria' => $this->memoriasService->obtenerPorId($id),
            'autores' => Autor::with(['persona', 'tipoAutor'])->get(),
        ]);
    }

    public function update(UpdateMemoriasInvestigacionRequest $request, int|MemoriasInvestigacion $memorias_investigacion): RedirectResponse
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;

        $this->memoriasService->actualizar(
            $id,
            $request->validated(),
            $request->file('mdi_archivo')
        );

        return redirect()->route('admin.memorias-investigacion.index')
            ->with('success', 'Memoria de investigación actualizada exitosamente.');
    }

    public function destroy(int|MemoriasInvestigacion $memorias_investigacion): RedirectResponse
    {
        $id = $memorias_investigacion instanceof MemoriasInvestigacion ? $memorias_investigacion->id : (int) $memorias_investigacion;
        $this->memoriasService->eliminar($id);

        return redirect()->route('admin.memorias-investigacion.index')
            ->with('success', 'Memoria de investigación eliminada exitosamente.');
    }

    public function plantilla(): BinaryFileResponse
    {
        return Excel::download(
            new MemoriasInvestigacionPlantillaExport,
            'plantilla_memorias_investigacion.xlsx'
        );
    }

    public function export(Request $request): BinaryFileResponse
    {
        $timestamp = date('Ymd_His');

        return Excel::download(
            new MemoriasInvestigacionExport($request),
            "memorias_investigacion_export_{$timestamp}.xlsx"
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo_excel' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:15360'],
        ], [
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel o CSV.',
            'archivo_excel.mimes' => 'El archivo debe tener formato .xlsx, .xls o .csv.',
            'archivo_excel.max' => 'El archivo no debe exceder los 15 MB.',
        ]);

        $import = new MemoriasInvestigacionImport;
        Excel::import($import, $request->file('archivo_excel'));

        $importados = $import->getImportedCount();
        $omitidos = $import->getSkippedCount();
        $errores = $import->getErrors();

        if ($importados > 0 && empty($errores)) {
            return redirect()->route('admin.memorias-investigacion.index')
                ->with('success', "Se importaron exitosamente {$importados} registro(s) de memorias y proyectos de investigación.");
        }

        if ($importados > 0 && ! empty($errores)) {
            return redirect()->route('admin.memorias-investigacion.index')
                ->with('warning', "Se procesaron {$importados} registro(s), pero hubo {$omitidos} observación(es) en el archivo.")
                ->with('import_errors', $errores);
        }

        return redirect()->route('admin.memorias-investigacion.index')
            ->with('error', 'No se pudo importar ningún registro. Verifique el formato del archivo Excel.')
            ->with('import_errors', $errores);
    }
}
