<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        if (!$user->esAdmin()) {
            // Log del intento de acceso no autorizado
            if (class_exists(\App\Models\AuditoriaLog::class)) {
                \App\Models\AuditoriaLog::registrar(
                    'auth',
                    'acceso_denegado',
                    'ruta',
                    null,
                    $request->path(),
                    null,
                    ['url' => $request->fullUrl(), 'rol' => $user->rol],
                    "Cajero {$user->name} intentó acceder a ruta de admin: /" . $request->path(),
                    'warning'
                );
            }
            
            return redirect('/dashboard')->with('error', '⛔ Esta sección es solo para administradores');
        }
        
        return $next($request);
    }
}
