<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotariaSubdominio
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->empresa && $user->empresa->industry_type === 'notaria') {
            $host = $request->getHost();
            // Si está en la IP o en otro dominio que no sea el subdominio de notaría
            if ($host !== 'notaria.nexposolution.com') {
                return redirect('https://notaria.nexposolution.com' . $request->getRequestUri());
            }
        }

        return $next($request);
    }
}
