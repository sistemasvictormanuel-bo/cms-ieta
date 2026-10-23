<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArticulosExport;
use App\Exports\ArticulosPlantillaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticuloRequest;
use App\Http\Requests\UpdateArticuloRequest;
use App\Imports\ArticulosImport;
use App\Models\Articulo;
use App\Models\Autor;
use App\Models\TipoParticipacion;
use App\Services\ArticuloService;
use App\Services\EdicionVarianzaService;
use App\Services\EtiquetaArtService;
use App\Services\TipoArticuloService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArticuloController extends Controller
{
    public function __construct(
        protected ArticuloService $articuloService,
        protected EtiquetaArtService $etiquetaService,
        protected TipoArticuloService $tipoArticuloService,
        protected EdicionVarianzaService $edicionVarianzaService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Articulo::class);

        $user = $request->user();
        $isAutor = $user && $user->hasRole('autor');
        $autorId = $isAutor ? $user->autor()?->id : null;
        $userId = $isAutor ? $user->id : null;

        $search = $request->input('search');
        $tipoId = $request->filled('tipo_id') ? (int) $request->input('tipo_id') : null;
        $edicionId = $request->filled('edicion_id') ? (int) $request->input('edicion_id') : null;
        $estadoRevision = $request->input('estado_revision');
        $perPage = (int) $request->input('per_page', 10);

        return Inertia::render('Admin/Articulos/Index', [
            'articulos' => $this->articuloService->obtenerTodos(
                $search,
                $tipoId,
                $edicionId,
                $perPage,
                $autorId,
                $userId,
                $estadoRevision
            ),
            'tipos_articulos' => $this->tipoArticuloService->obtenerListaParaSelect(),
            'ediciones' => $this->edicionVarianzaService->obtenerListaParaSelect(),
            'filters' => [
                'search' => $search,
                'tipo_id' => $tipoId,
                'edicion_id' => $edicionId,
                'estado_revision' => $estadoRevision,
                'per_page' => $perPage,
            ],
            'puede_revisar' => $user && ($user->hasRole('administrador') || $user->can('articulos.revisar')),
            'es_autor' => $isAutor,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Articulo::class);

        $user = auth()->user();
        $autorActual = $user?->autor();

        return Inertia::render('Admin/Articulos/Create', [
            'autores' => Autor::with(['persona', 'gradoAcademico'])
                ->where('at_estado', true)
                ->get(),
            'participaciones' => TipoParticipacion::orderBy('tp_nombre')->get(),
            'etiquetas' => $this->etiquetaService->obtenerListaParaSelect(),
            'tipos_articulos' => $this->tipoArticuloService->obtenerListaParaSelect(),
            'ediciones' => $this->edicionVarianzaService->obtenerListaParaSelect(),
            'autor_actual' => $autorActual,
            'es_autor' => $user && $user->hasRole('autor') && ! $user->hasRole('administrador'),
            'puede_revisar' => $user && ($user->hasRole('administrador') || $user->can('articulos.revisar')),
        ]);
    }

    public function store(StoreArticuloRequest $request): RedirectResponse
    {
        Gate::authorize('create', Articulo::class);

        $this->articuloService->guardar(
            $request->validated(),
            $request->file('art_archivo')
        );

        $mensaje = auth()->user()?->hasRole('autor')
            ? 'Artículo enviado exitosamente para revisión editorial.'
            : 'Artículo científico creado exitosamente.';

        return redirect()->route('admin.articulos.index')
            ->with('success', $mensaje);
    }

    public function show(int|Articulo $articulo): Response
    {
        $id = $articulo instanceof Articulo ? $articulo->id : (int) $articulo;
        $articuloModel = $this->articuloService->obtenerPorId($id);

        Gate::authorize('view', $articuloModel);

        $user = auth()->user();

        return Inertia::render('Admin/Articulos/Show', [
            'articulo' => $articuloModel->load(['historialRevisiones.user', 'revisadoPor', 'creadoPor']),
            'participaciones' => TipoParticipacion::orderBy('tp_nombre')->get(),
            'similares' => $this->articuloService->obtenerSimilares($articuloModel, 4),
            'mas_leidos_autor' => $this->articuloService->obtenerMasLeidosMismoAutor($articuloModel, 4),
            'puede_revisar' => $user && ($user->hasRole('administrador') || $user->can('articulos.revisar')),
            'es_autor' => $user && $user->hasRole('autor'),
        ]);
    }

    public function edit(int|Articulo $articulo): Response
    {
        $id = $articulo instanceof Articulo ? $articulo->id : (int) $articulo;
        $articuloModel = $this->articuloService->obtenerPorId($id);

        Gate::authorize('update', $articuloModel);

        $user = auth()->user();

        return Inertia::render('Admin/Articulos/Edit', [
            'articulo' => $articuloModel,
            'autores' => Autor::with(['persona', 'gradoAcademico'])
                ->where('at_estado', true)
                ->get(),
            'participaciones' => TipoParticipacion::orderBy('tp_nombre')->get(),
            'etiquetas' => $this->etiquetaService->obtenerListaParaSelect(),
            'tipos_articulos' => $this->tipoArticuloService->obtenerListaParaSelect(),
            'ediciones' => $this->edicionVarianzaService->obtenerListaParaSelect(),
            'es_autor' => $user && $user->hasRole('autor') && ! $user->hasRole('administrador'),
            'puede_revisar' => $user && ($user->hasRole('administrador') || $user->can('articulos.revisar')),
        ]);
    }

    public function update(UpdateArticuloRequest $request, int|Articulo $articulo): RedirectResponse
    {
        $id = $articulo instanceof Articulo ? $articulo->id : (int) $articulo;
        $articuloModel = $this->articuloService->obtenerPorId($id);

        Gate::authorize('update', $articuloModel);

        $this->articuloService->actualizar(
            $id,
            $request->validated(),
            $request->file('art_archivo')
        );

        $mensaje = auth()->user()?->hasRole('autor')
            ? 'Artículo científico actualizado y re-enviado a revisión.'
            : 'Artículo científico actualizado exitosamente.';

        return redirect()->route('admin.articulos.index')
            ->with('success', $mensaje);
    }

    public function destroy(int|Articulo $articulo): RedirectResponse
    {
        $id = $articulo instanceof Articulo ? $articulo->id : (int) $articulo;
        $articuloModel = $this->articuloService->obtenerPorId($id);

        Gate::authorize('delete', $articuloModel);

        $this->articuloService->eliminar($id);

        return redirect()->route('admin.articulos.index')
            ->with('success', 'Artículo científico eliminado exitosamente.');
    }

    public function aprobar(Request $request, int|Articulo $articulo): RedirectResponse
    {
        $id = $articulo instanceof Articulo ? $articulo->id : (int) $articulo;
        $articuloModel = $this->articuloService->obtenerPorId($id);

        Gate::authorize('revisar', $articuloModel);

        $this->articuloService->aprobar($id, $request->user()->id, $request->input('motivo'));

        return redirect()->back()
            ->with('success', "El artículo '{$articuloModel->art_titulo}' fue aprobado y publicado.");
    }

    public function rechazar(Request $request, int|Articulo $articulo): RedirectResponse
    {
        $id = $articulo instanceof Articulo ? $articulo->id : (int) $articulo;
        $articuloModel = $this->articuloService->obtenerPorId($id);

        Gate::authorize('revisar', $articuloModel);

        $request->validate([
            'motivo' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'motivo.required' => 'Debes especificar el motivo del rechazo.',
            'motivo.min' => 'El motivo debe tener al menos 3 caracteres.',
        ]);

        $this->articuloService->rechazar($id, $request->user()->id, $request->input('motivo'));

        return redirect()->back()
            ->with('warning', "El artículo '{$articuloModel->art_titulo}' fue rechazado con observaciones.");
    }

    public function export(Request $request): BinaryFileResponse
    {
        return Excel::download(new ArticulosExport($request), 'articulos_'.date('Y-m-d').'.xlsx');
    }

    public function plantilla(): BinaryFileResponse
    {
        return Excel::download(new ArticulosPlantillaExport, 'plantilla_articulos.xlsx');
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

        $import = new ArticulosImport;
        Excel::import($import, $request->file('archivo_excel'));

        $importados = $import->getImportedCount();
        $omitidos = $import->getSkippedCount();
        $errores = $import->getErrors();

        if ($importados > 0 && empty($errores)) {
            return redirect()->route('admin.articulos.index')
                ->with('success', "Se importaron exitosamente {$importados} artículo(s) científico(s).");
        }

        if ($importados > 0 && ! empty($errores)) {
            return redirect()->route('admin.articulos.index')
                ->with('warning', "Se importaron {$importados} artículo(s), pero hubo {$omitidos} fila(s) con observaciones.")
                ->with('import_errors', $errores);
        }

        return redirect()->route('admin.articulos.index')
            ->with('error', 'No se pudo importar ningún artículo. Se detectaron observaciones en el archivo.')
            ->with('import_errors', $errores);
    }
}
