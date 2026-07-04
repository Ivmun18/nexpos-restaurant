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
            $path = $request->getPathInfo();

            if ($host === 'notaria.nexposolution.com') {
                return $next($request);
            }

            $excluir = ['/login', '/logout', '/sanctum', '/api', '/_inertia', '/build'];
            foreach ($excluir as $ex) {
                if (str_starts_with($path, $ex)) {
                    return $next($request);
                }
            }

            return redirect('https://notaria.nexposolution.com' . $request->getRequestUri());
        }

        return $next($request);
    }
}
