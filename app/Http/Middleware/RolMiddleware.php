<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();
        $userRol = $user->rol;
        $industryType = $user->empresa->industry_type ?? 'restaurante';

        // Solo aplicar control de roles en restaurante
        if ($industryType !== 'restaurante') {
            return $next($request);
        }

        // Admin siempre tiene acceso
        if ($userRol === 'admin' || $userRol === 'superadmin') {
            return $next($request);
        }

        if (!in_array($userRol, $roles)) {
            return redirect('/dashboard')->with('error', 'No tienes permisos para acceder a esa sección.');
        }

        return $next($request);
    }
}
