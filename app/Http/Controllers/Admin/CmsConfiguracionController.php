<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCmsConfiguracionRequest;
use App\Services\CmsConfiguracionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CmsConfiguracionController extends Controller
{
    public function __construct(
        protected CmsConfiguracionService $configuracionService
    ) {}

    /**
     * Muestra el formulario para editar la configuración general e identidad institucional (Singleton).
     */
    public function edit(): Response
    {
        return Inertia::render('Admin/Configuracion/Edit', [
            'configuracion' => $this->configuracionService->obtener(),
        ]);
    }

    /**
     * Actualiza la configuración institucional, logos, redes sociales y metadatos.
     */
    public function update(UpdateCmsConfiguracionRequest $request): RedirectResponse
    {
        $this->configuracionService->actualizar(
            $request->validated(),
            $request->file('cfg_logo_principal'),
            $request->file('cfg_logo_blanco'),
            $request->file('cfg_favicon')
        );

        return redirect()->route('admin.configuracion.edit')
            ->with('success', 'Configuración institucional actualizada correctamente.');
    }
}
