<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateContactoRequest;
use App\Services\CmsContactoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactoController extends Controller
{
    public function __construct(
        protected CmsContactoService $contactoService
    ) {}

    /**
     * Muestra el formulario para editar la información institucional de Contacto (Singleton).
     */
    public function edit(): Response
    {
        return Inertia::render('Admin/Contacto/Edit', [
            'contacto' => $this->contactoService->obtener(),
        ]);
    }

    /**
     * Actualiza el registro institucional único de Contacto solo un registro.
     */
    public function update(UpdateContactoRequest $request): RedirectResponse
    {
        $this->contactoService->actualizar($request->validated());

        return redirect()->route('admin.contacto.edit')
            ->with('success', 'Información de contacto institucional actualizada correctamente.');
    }
}
