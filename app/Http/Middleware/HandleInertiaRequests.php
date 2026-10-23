<?php

namespace App\Http\Middleware;

use App\Models\Articulo;
use App\Models\CmsConfiguracion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url,
                    'is_active' => (bool) $user->is_active,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'is_admin' => $user->hasRole('administrador'),
                    'is_autor' => $user->hasRole('autor'),
                    'two_factor_enabled' => ! is_null($user->two_factor_secret),
                    'two_factor_confirmed' => ! is_null($user->two_factor_confirmed_at),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'institucion' => function () {
                $config = CmsConfiguracion::first();

                return [
                    'nombre' => $config?->cfg_nombre_institucion ?? 'Instituto de Estadística Teórica y Aplicada',
                    'siglas' => $config?->cfg_siglas ?? 'IETA',
                    'slogan' => $config?->cfg_slogan ?? 'Estadística • UMSA',
                    'logo_url' => $config?->logo_principal_url,
                    'logo_blanco_url' => $config?->logo_blanco_url,
                    'favicon_url' => $config?->favicon_url,
                ];
            },
            'solicitudes_pendientes' => function () use ($user) {
                if (! $user) {
                    return [];
                }

                $puedeRevisar = $user->hasRole('administrador') || $user->can('articulos.revisar');

                if ($puedeRevisar) {
                    return Articulo::with([
                        'tipoArticulo',
                        'creadoPor',
                        'autores.persona',
                    ])
                        ->where('estado_revision', 'pendiente')
                        ->latest('id')
                        ->take(20)
                        ->get()
                        ->map(function ($art) {
                            $autoresNombres = $art->autores
                                ->map(fn ($a) => $a->persona ? trim("{$a->persona->p_nombre} {$a->persona->p_apellido_paterno}") : null)
                                ->filter()
                                ->values()
                                ->all();

                            return [
                                'id' => $art->id,
                                'art_titulo' => $art->art_titulo,
                                'art_slug' => $art->art_slug,
                                'art_resumen' => $art->art_resumen ? Str::limit(strip_tags($art->art_resumen), 140) : null,
                                'tipo_articulo' => $art->tipoArticulo?->tar_nombre ?? 'Artículo Científico',
                                'created_at' => $art->created_at?->toISOString(),
                                'creado_por' => $art->creadoPor ? [
                                    'id' => $art->creadoPor->id,
                                    'name' => $art->creadoPor->name,
                                    'email' => $art->creadoPor->email,
                                ] : null,
                                'autores_texto' => ! empty($autoresNombres) ? implode(', ', $autoresNombres) : ($art->creadoPor?->name ?? 'Autor'),
                                'archivo_url' => $art->archivo_url,
                                'estado_revision' => $art->estado_revision,
                            ];
                        })->values()->all();
                }

                if ($user->hasRole('autor')) {
                    return Articulo::with(['tipoArticulo'])
                        ->where('created_by_user_id', $user->id)
                        ->latest('updated_at')
                        ->take(10)
                        ->get()
                        ->map(function ($art) {
                            return [
                                'id' => $art->id,
                                'art_titulo' => $art->art_titulo,
                                'art_slug' => $art->art_slug,
                                'tipo_articulo' => $art->tipoArticulo?->tar_nombre ?? 'Artículo Científico',
                                'estado_revision' => $art->estado_revision,
                                'revision_motivo' => $art->revision_motivo,
                                'revisado_at' => $art->revisado_at?->toISOString(),
                                'created_at' => $art->created_at?->toISOString(),
                                'updated_at' => $art->updated_at?->toISOString(),
                                'archivo_url' => $art->archivo_url,
                            ];
                        })->values()->all();
                }

                return [];
            },
            'solicitudes_pendientes_count' => function () use ($user) {
                if (! $user) {
                    return 0;
                }
                if ($user->hasRole('administrador') || $user->can('articulos.revisar')) {
                    return Articulo::where('estado_revision', 'pendiente')->count();
                }
                if ($user->hasRole('autor')) {
                    return Articulo::where('created_by_user_id', $user->id)
                        ->where('estado_revision', 'pendiente')
                        ->count();
                }

                return 0;
            },
        ];
    }
}
