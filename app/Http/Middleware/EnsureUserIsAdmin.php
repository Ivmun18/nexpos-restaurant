<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->rol !== 'admin') {
            abort(403, 'Solo administradores pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
