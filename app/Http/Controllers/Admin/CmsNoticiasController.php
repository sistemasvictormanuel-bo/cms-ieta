<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsNoticiasRequest;
use App\Http\Requests\UpdateCmsNoticiasRequest;
use App\Models\CmsNoticias;
use App\Models\Persona;
use App\Services\CmsNoticiasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsNoticiasController extends Controller
{
    public function __construct(
        protected CmsNoticiasService $noticiasService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $publicado = $request->has('publicado') && $request->input('publicado') !== ''
            ? filter_var($request->input('publicado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $destacado = $request->has('destacado') && $request->input('destacado') !== ''
            ? filter_var($request->input('destacado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $personaId = $request->filled('persona_id') ? (int) $request->input('persona_id') : null;
        $perPage = (int) $request->input('per_page', 10);

        $stats = [
            'total' => CmsNoticias::count(),
            'publicadas' => CmsNoticias::where('not_publicado', true)->count(),
            'borradores' => CmsNoticias::where('not_publicado', false)->count(),
            'destacadas' => CmsNoticias::where('not_destacado', true)->count(),
        ];

        return Inertia::render('Admin/Noticias/Index', [
            'noticias' => $this->noticiasService->obtenerTodos($search, $publicado, $destacado, $personaId, $perPage),
            'personas' => Persona::orderBy('p_nombre')->get(),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'publicado' => $request->input('publicado'),
                'destacado' => $request->input('destacado'),
                'persona_id' => $personaId,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Noticias/Create', [
            'personas' => Persona::orderBy('p_nombre')->get(),
        ]);
    }

    public function store(StoreCmsNoticiasRequest $request): RedirectResponse
    {
        $this->noticiasService->guardar(
            $request->validated(),
            $request->file('not_portada')
        );

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia creada exitosamente.');
    }

    public function show(int|CmsNoticias $noticia): Response
    {
        $id = $noticia instanceof CmsNoticias ? $noticia->id : (int) $noticia;

        return Inertia::render('Admin/Noticias/Show', [
            'noticia' => $this->noticiasService->obtenerPorId($id),
        ]);
    }

    public function edit(int|CmsNoticias $noticia): Response
    {
        $id = $noticia instanceof CmsNoticias ? $noticia->id : (int) $noticia;

        return Inertia::render('Admin/Noticias/Edit', [
            'noticia' => $this->noticiasService->obtenerPorId($id),
            'personas' => Persona::orderBy('p_nombre')->get(),
        ]);
    }

    public function update(UpdateCmsNoticiasRequest $request, int|CmsNoticias $noticia): RedirectResponse
    {
        $id = $noticia instanceof CmsNoticias ? $noticia->id : (int) $noticia;

        $this->noticiasService->actualizar(
            $id,
            $request->validated(),
            $request->file('not_portada')
        );

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia actualizada exitosamente.');
    }

    public function destroy(int|CmsNoticias $noticia): RedirectResponse
    {
        $id = $noticia instanceof CmsNoticias ? $noticia->id : (int) $noticia;
        $this->noticiasService->eliminar($id);

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia eliminada exitosamente.');
    }

    public function toggleDestacado(int $id): RedirectResponse
    {
        $noticia = $this->noticiasService->toggleDestacado($id);
        $estado = $noticia->not_destacado ? 'destacada' : 'retirada de destacadas';

        return back()->with('success', "La noticia ha sido {$estado}.");
    }

    public function togglePublicado(int $id): RedirectResponse
    {
        $noticia = $this->noticiasService->togglePublicado($id);
        $estado = $noticia->not_publicado ? 'publicada' : 'puesta en borrador';

        return back()->with('success', "La noticia ha sido {$estado}.");
    }
}
