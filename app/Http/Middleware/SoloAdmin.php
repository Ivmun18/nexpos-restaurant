<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SoloAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->rol === 'cajero') {
            return redirect('/dashboard')->with('error', 'No tienes permisos para acceder a esa sección.');
        }

        return $next($request);
    }
}