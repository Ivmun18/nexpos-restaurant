<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PuedeFacturarMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->puedeFacturar()) {
            return $next($request);
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success'  => false,
                'mensaje'  => 'No tiene permiso para emitir comprobantes.',
            ], 403);
        }

        return redirect()->back()->with('error', 'No tiene permiso para emitir comprobantes.');
    }
}
