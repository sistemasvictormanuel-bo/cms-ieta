<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CmsBancoDatosExport;
use App\Exports\CmsBancoDatosPlantillaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsBancoDatosRequest;
use App\Http\Requests\UpdateCmsBancoDatosRequest;
use App\Imports\CmsBancoDatosImport;
use App\Models\AreaTematicas;
use App\Models\CmsBancoDatos;
use App\Services\CmsBancoDatosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CmsBancoDatosController extends Controller
{
    public function __construct(
        protected CmsBancoDatosService $bancoDatosService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/BancoDatos/Index', [
            'bancoDatos' => $this->bancoDatosService->obtenerTodos($search, $tipo, $perPage),
            'filters' => [
                'search' => $search,
                'tipo' => $tipo,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/BancoDatos/Create', [
            'areas' => AreaTematicas::orderBy('at_nombre')->get(),
        ]);
    }

    public function store(StoreCmsBancoDatosRequest $request): RedirectResponse
    {
        $this->bancoDatosService->guardar(
            $request->validated(),
            $request->file('bdd_archivo'),
            $request->file('bdd_portada')
        );

        return redirect()->route('admin.banco-datos.index')
            ->with('success', 'Base de datos registrada exitosamente en el Banco de Datos.');
    }

    public function show(int|CmsBancoDatos $banco_dato): Response
    {
        $id = $banco_dato instanceof CmsBancoDatos ? $banco_dato->id : (int) $banco_dato;

        return Inertia::render('Admin/BancoDatos/Show', [
            'banco' => $this->bancoDatosService->obtenerPorId($id),
        ]);
    }

    public function edit(int|CmsBancoDatos $banco_dato): Response
    {
        $id = $banco_dato instanceof CmsBancoDatos ? $banco_dato->id : (int) $banco_dato;

        return Inertia::render('Admin/BancoDatos/Edit', [
            'banco' => $this->bancoDatosService->obtenerPorId($id),
            'areas' => AreaTematicas::orderBy('at_nombre')->get(),
        ]);
    }

    public function update(UpdateCmsBancoDatosRequest $request, int|CmsBancoDatos $banco_dato): RedirectResponse
    {
        $id = $banco_dato instanceof CmsBancoDatos ? $banco_dato->id : (int) $banco_dato;

        $this->bancoDatosService->actualizar(
            $id,
            $request->validated(),
            $request->file('bdd_archivo'),
            $request->file('bdd_portada')
        );

        return redirect()->route('admin.banco-datos.index')
            ->with('success', 'Base de datos actualizada exitosamente.');
    }

    public function destroy(int|CmsBancoDatos $banco_dato): RedirectResponse
    {
        $id = $banco_dato instanceof CmsBancoDatos ? $banco_dato->id : (int) $banco_dato;
        $this->bancoDatosService->eliminar($id);

        return redirect()->route('admin.banco-datos.index')
            ->with('success', 'Base de datos eliminada exitosamente.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $tipoPrefix = $request->filled('tipo') ? Str::slug($request->input('tipo')).'_' : '';
        $filename = 'banco_datos_'.$tipoPrefix.date('Y-m-d').'.xlsx';

        return Excel::download(new CmsBancoDatosExport($request), $filename);
    }

    public function plantilla(): BinaryFileResponse
    {
        return Excel::download(new CmsBancoDatosPlantillaExport, 'plantilla_banco_datos.xlsx');
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

        $import = new CmsBancoDatosImport;
        Excel::import($import, $request->file('archivo_excel'));

        $importados = $import->getImportedCount();
        $omitidos = $import->getSkippedCount();
        $errores = $import->getErrors();

        if ($importados > 0 && empty($errores)) {
            return redirect()->route('admin.banco-datos.index')
                ->with('success', "Se importaron exitosamente {$importados} base(s) de datos (censos/encuestas).");
        }

        if ($importados > 0 && ! empty($errores)) {
            return redirect()->route('admin.banco-datos.index')
                ->with('warning', "Se procesaron {$importados} base(s) de datos, pero hubo {$omitidos} fila(s) con observaciones.")
                ->with('import_errors', $errores);
        }

        return redirect()->route('admin.banco-datos.index')
            ->with('error', 'No se pudo importar ningún registro. Verifique el formato de las columnas en la plantilla.')
            ->with('import_errors', $errores);
    }
}
