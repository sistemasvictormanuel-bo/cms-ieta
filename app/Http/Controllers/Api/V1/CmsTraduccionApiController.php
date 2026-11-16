<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CmsTraduccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsTraduccionApiController extends Controller
{
    /**
     * Retorna el diccionario completo o filtrado de traducciones para el sitio público.
     *
     * @queryParam lang string Idioma solicitado: 'es', 'en' o omitir para ambos. Example: en
     * @queryParam modulo string Sección o módulo opcional (ej: navegacion, hero, revista, banco_datos). Example: navegacion
     */
    public function index(Request $request): JsonResponse
    {
        $lang = $request->query('lang');
        $modulo = $request->query('modulo');

        $diccionario = CmsTraduccion::getDiccionario($lang, $modulo);

        return response()->json([
            'status' => 'success',
            'lang' => $lang ? strtolower($lang) : 'all',
            'modulo' => $modulo ?: 'all',
            'data' => $diccionario,
        ]);
    }

    /**
     * Retorna el diccionario de un módulo específico.
     *
     * @param  string  $modulo  Nombre del módulo (navegacion, hero, revista, banco_datos, etc.)
     */
    public function showModulo(Request $request, string $modulo): JsonResponse
    {
        $lang = $request->query('lang');
        $diccionario = CmsTraduccion::getDiccionario($lang, $modulo);

        return response()->json([
            'status' => 'success',
            'modulo' => $modulo,
            'lang' => $lang ? strtolower($lang) : 'all',
            'data' => $diccionario,
        ]);
    }
}
