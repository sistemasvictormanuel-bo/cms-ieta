<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsGaleriaRequest;
use App\Http\Requests\UpdateCmsGaleriaRequest;
use App\Models\CmsGaleria;
use App\Services\CmsGaleriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsGaleriaController extends Controller
{
    public function __construct(
        protected CmsGaleriaService $galeriaService
    ) {}

    /**
     * Muestra el listado de imágenes con soporte de vista de tarjetas o tabla.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $categoria = $request->input('categoria');
        $destacado = $request->has('destacado') && $request->input('destacado') !== ''
            ? filter_var($request->input('destacado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $activo = $request->has('activo') && $request->input('activo') !== ''
            ? filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $perPage = (int) $request->input('per_page', 12);

        return Inertia::render('Admin/Galeria/Index', [
            'imagenes' => $this->galeriaService->obtenerTodos(
                $search,
                $categoria,
                $destacado,
                $activo,
                $perPage
            ),
            'categorias' => $this->galeriaService->obtenerCategorias(),
            'stats' => $this->galeriaService->obtenerEstadisticas(),
            'filters' => [
                'search' => $search,
                'categoria' => $categoria,
                'destacado' => $request->input('destacado'),
                'activo' => $request->input('activo'),
                'per_page' => $perPage,
                'view' => $request->input('view', 'cards'),
            ],
        ]);
    }

    /**
     * Formulario para registrar una nueva imagen en la galería.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Galeria/Create', [
            'categoriasSugeridas' => $this->galeriaService->obtenerCategorias(),
        ]);
    }

    /**
     * Guarda una nueva imagen.
     */
    public function store(StoreCmsGaleriaRequest $request): RedirectResponse
    {
        $archivo = $request->file('imagen') ?? $request->file('gal_imagen');

        $this->galeriaService->guardar(
            $request->validated(),
            $archivo
        );

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Imagen agregada a la galería correctamente.');
    }

    /**
     * Detalle de la imagen.
     */
    public function show(int|CmsGaleria $galerium): Response
    {
        $id = $galerium instanceof CmsGaleria ? $galerium->id : (int) $galerium;

        return Inertia::render('Admin/Galeria/Show', [
            'imagen' => $this->galeriaService->obtenerPorId($id),
        ]);
    }

    /**
     * Formulario para editar una imagen.
     */
    public function edit(int|CmsGaleria $galerium): Response
    {
        $id = $galerium instanceof CmsGaleria ? $galerium->id : (int) $galerium;

        return Inertia::render('Admin/Galeria/Edit', [
            'imagen' => $this->galeriaService->obtenerPorId($id),
            'categoriasSugeridas' => $this->galeriaService->obtenerCategorias(),
        ]);
    }

    /**
     * Actualiza la información y/o imagen.
     */
    public function update(UpdateCmsGaleriaRequest $request, int|CmsGaleria $galerium): RedirectResponse
    {
        $id = $galerium instanceof CmsGaleria ? $galerium->id : (int) $galerium;
        $archivo = $request->file('imagen') ?? $request->file('gal_imagen');

        $this->galeriaService->actualizar(
            $id,
            $request->validated(),
            $archivo
        );

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Imagen de la galería actualizada correctamente.');
    }

    /**
     * Elimina el registro y su archivo asociado.
     */
    public function destroy(int|CmsGaleria $galerium): RedirectResponse
    {
        $id = $galerium instanceof CmsGaleria ? $galerium->id : (int) $galerium;
        $this->galeriaService->eliminar($id);

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Imagen eliminada de la galería exitosamente.');
    }

    /**
     * Alterna el estado destacado.
     */
    public function toggleDestacado(int $id): RedirectResponse
    {
        $imagen = $this->galeriaService->toggleDestacado($id);
        $estado = $imagen->gal_destacado ? 'marcada como destacada' : 'retirada de destacadas';

        return back()->with('success', "La imagen ha sido {$estado}.");
    }

    /**
     * Alterna el estado activo.
     */
    public function toggleActivo(int $id): RedirectResponse
    {
        $imagen = $this->galeriaService->toggleActivo($id);
        $estado = $imagen->gal_activo ? 'activada para visualización pública' : 'desactivada del portal público';

        return back()->with('success', "La imagen ha sido {$estado}.");
    }
}
