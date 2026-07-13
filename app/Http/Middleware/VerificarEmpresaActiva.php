<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarEmpresaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->empresa && $user->empresa->activo == 0) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu suscripcion ha vencido. Contacta a soporte NEXPOS para renovar tu plan: +51 982 383 648',
            ]);
        }

        return $next($request);
    }
}
