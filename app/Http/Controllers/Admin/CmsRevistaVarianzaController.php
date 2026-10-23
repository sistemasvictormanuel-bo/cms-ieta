<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCmsRevistaVarianzaRequest;
use App\Services\CmsRevistaVarianzaService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CmsRevistaVarianzaController extends Controller
{
    public function __construct(
        protected CmsRevistaVarianzaService $revistaService
    ) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/RevistaVarianza/Edit', [
            'revista' => $this->revistaService->obtener(),
        ]);
    }

    public function update(UpdateCmsRevistaVarianzaRequest $request): RedirectResponse
    {
        $this->revistaService->actualizar(
            $request->validated(),
            $request->file('rv_portada')
        );

        return redirect()->route('admin.revista-varianza.edit')
            ->with('success', 'Información institucional de Revista Varianza actualizada exitosamente.');
    }
}
