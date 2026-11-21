<?php

use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'ensure.2fa' => EnsureTwoFactorEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $mensaje = 'El archivo supera el tamaño máximo permitido para subida (20 MB). Te recomendamos optimizar el documento o utilizar la opción de enlace externo (Google Drive / Nube).';

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $mensaje,
                ], 413);
            }

            return back()->with('error', $mensaje)->withErrors([
                'archivo' => $mensaje,
                'art_archivo' => $mensaje,
                'mdi_archivo' => $mensaje,
                'ev_archivo' => $mensaje,
                'bdd_archivo' => $mensaje,
            ]);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && ! $request->is('api/*') && ! $request->expectsJson()) {
                return Inertia::render('Errors/Forbidden', [
                    'message' => $e->getMessage() ?: 'No tienes los permisos necesarios para acceder a esta sección.',
                ])->toResponse($request)->setStatusCode(403);
            }

            return null;
        });
    })->create();
