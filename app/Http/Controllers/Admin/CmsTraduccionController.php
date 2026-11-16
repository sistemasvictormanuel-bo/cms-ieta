<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsTraduccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CmsTraduccionController extends Controller
{
    /**
     * Muestra el panel de gestión de traducciones con buscador y filtrado por pestañas.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $modulo = $request->input('modulo');
        $perPage = (int) $request->input('per_page', 25);

        $query = CmsTraduccion::query()->orderBy('modulo')->orderBy('clave');

        if ($modulo !== null && $modulo !== '' && $modulo !== 'todos') {
            $query->where('modulo', $modulo);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('clave', 'like', "%{$term}%")
                    ->orWhere('texto_es', 'like', "%{$term}%")
                    ->orWhere('texto_en', 'like', "%{$term}%")
                    ->orWhere('descripcion', 'like', "%{$term}%");
            });
        }

        $traducciones = $query->paginate($perPage)->withQueryString();

        $modulosWithCount = CmsTraduccion::selectRaw('modulo, count(*) as count')
            ->groupBy('modulo')
            ->orderBy('modulo')
            ->get();

        $totalTraducciones = CmsTraduccion::count();

        return Inertia::render('Admin/Traducciones/Index', [
            'traducciones' => $traducciones,
            'modulosWithCount' => $modulosWithCount,
            'totalTraducciones' => $totalTraducciones,
            'filters' => [
                'search' => $search,
                'modulo' => $modulo ?: 'todos',
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Registra una nueva clave de traducción.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'modulo' => ['required', 'string', 'max:50'],
            'clave' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-\.]+$/',
                Rule::unique('cms_traducciones')->where(function ($query) use ($request) {
                    return $query->where('modulo', $request->input('modulo'));
                }),
            ],
            'texto_es' => ['required', 'string'],
            'texto_en' => ['required', 'string'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'clave.regex' => 'La clave solo puede contener letras, números, guiones y puntos (sin espacios).',
            'clave.unique' => 'Esta clave ya existe dentro del módulo seleccionado.',
            'texto_es.required' => 'El texto en español es obligatorio.',
            'texto_en.required' => 'El texto en inglés es obligatorio.',
        ]);

        CmsTraduccion::create($validated);

        return redirect()->route('admin.traducciones.index', ['modulo' => $validated['modulo']])
            ->with('success', "Traducción '{$validated['clave']}' creada exitosamente.");
    }

    /**
     * Actualiza una clave existente.
     */
    public function update(Request $request, CmsTraduccion $traduccione): RedirectResponse
    {
        $validated = $request->validate([
            'modulo' => ['required', 'string', 'max:50'],
            'clave' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-\.]+$/',
                Rule::unique('cms_traducciones')->where(function ($query) use ($request) {
                    return $query->where('modulo', $request->input('modulo'));
                })->ignore($traduccione->id),
            ],
            'texto_es' => ['required', 'string'],
            'texto_en' => ['required', 'string'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $traduccione->update($validated);

        return redirect()->back()
            ->with('success', "Traducción '{$traduccione->clave}' actualizada correctamente.");
    }

    /**
     * Elimina una traducción.
     */
    public function destroy(CmsTraduccion $traduccione): RedirectResponse
    {
        $clave = $traduccione->clave;
        $traduccione->delete();

        return redirect()->back()
            ->with('success', "Traducción '{$clave}' eliminada del sistema.");
    }

    /**
     * Exporta el diccionario completo en formato JSON para el equipo de Frontend.
     */
    public function exportJson(): HttpResponse
    {
        $diccionario = CmsTraduccion::getDiccionario();
        $json = json_encode($diccionario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $filename = 'traducciones_ieta_'.date('Y-m-d').'.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
