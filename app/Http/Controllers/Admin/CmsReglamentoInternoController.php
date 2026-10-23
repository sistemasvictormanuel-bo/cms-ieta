<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCmsReglamentoInternoRequest;
use App\Services\CmsReglamentoInternoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CmsReglamentoInternoController extends Controller
{
    public function __construct(
        protected CmsReglamentoInternoService $reglamentoService
    ) {}

    /**
     * Muestra la vista de administración del Reglamento Interno (Singleton).
     */
    public function edit(): Response
    {
        return Inertia::render('Admin/ReglamentoInterno/Edit', [
            'reglamento' => $this->reglamentoService->obtener(),
        ]);
    }

    /**
     * Actualiza el contenido y/o archivo PDF del reglamento institucional.
     */
    public function update(UpdateCmsReglamentoInternoRequest $request): RedirectResponse
    {
        $this->reglamentoService->actualizar(
            $request->validated(),
            $request->file('archivo_reglamento')
        );

        return redirect()->route('admin.reglamento-interno.edit')
            ->with('success', 'Reglamento interno actualizado correctamente.');
    }

    /**
     * Elimina el archivo PDF adjunto al reglamento institucional.
     */
    public function eliminarArchivo(): RedirectResponse
    {
        $this->reglamentoService->eliminarArchivo();

        return redirect()->route('admin.reglamento-interno.edit')
            ->with('success', 'El archivo PDF del reglamento fue eliminado exitosamente.');
    }
}
