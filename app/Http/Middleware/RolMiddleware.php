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

        $userRol = auth()->user()->rol;

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
