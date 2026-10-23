<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no está autenticado, continuar para que auth middleware lo maneje
        if (! $user) {
            return $next($request);
        }

        // Esta regla aplica para usuarios con rol 'autor' (o cualquier rol que deba forzar 2FA)
        if ($user->hasRole('autor') && ! $user->hasEnabledTwoFactorAuthentication()) {
            // Rutas y patrones excluidos del bloqueo:
            $allowedRoutes = [
                'two-factor.setup',
                'logout',
                'two-factor.login',
                'two-factor.challenge',
                'password.confirm',
                'password.confirmation',
            ];

            if ($request->routeIs($allowedRoutes)) {
                return $next($request);
            }

            // Excluir endpoints internos de Fortify para la habilitación de 2FA
            if (
                $request->is('user/two-factor-authentication*') ||
                $request->is('user/confirmed-two-factor-authentication*') ||
                $request->is('user/two-factor-qr-code*') ||
                $request->is('user/two-factor-secret-key*') ||
                $request->is('user/two-factor-recovery-codes*') ||
                $request->is('user/confirm-password*') ||
                $request->is('user/confirmed-password-status*') ||
                $request->is('logout')
            ) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Se requiere la configuración obligatoria de 2FA para autores.',
                    'requires_two_factor_setup' => true,
                    'redirect' => route('two-factor.setup'),
                ], 403);
            }

            return redirect()->route('two-factor.setup')->with(
                'flash.banner',
                'Por políticas de seguridad del IETA, es obligatorio activar la autenticación de dos factores (2FA) antes de utilizar el sistema.'
            );
        }

        return $next($request);
    }
}
