<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSobreNosotrosRequest;
use App\Services\CmsSobreNosotrosService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SobreNosotrosController extends Controller
{
    public function __construct(
        protected CmsSobreNosotrosService $sobreNosotrosService
    ) {}

    /**
     * Muestra el formulario para editar el registro único de Sobre Nosotros.
     */
    public function edit(): Response
    {
        return Inertia::render('Admin/SobreNosotros/Edit', [
            'sobreNosotros' => $this->sobreNosotrosService->obtener(),
        ]);
    }

    /**
     * Actualiza el registro único institucional.
     */
    public function update(UpdateSobreNosotrosRequest $request): RedirectResponse
    {
        $this->sobreNosotrosService->actualizar($request->validated());

        return redirect()->route('admin.sobre-nosotros.edit')
            ->with('success', 'Información institucional de "Sobre Nosotros" actualizada correctamente.');
    }
}
