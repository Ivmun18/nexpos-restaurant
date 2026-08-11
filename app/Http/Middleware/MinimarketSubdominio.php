<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MinimarketSubdominio
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user && $user->empresa && $user->empresa->industry_type === 'minimarket') {
            $path = $request->getPathInfo();

            if ($host === 'minimarket.nexposolution.com') {
                return $next($request);
            }

            $excluir = ['/login', '/logout', '/sanctum', '/api', '/_inertia', '/build'];
            foreach ($excluir as $ex) {
                if (str_starts_with($path, $ex)) {
                    return $next($request);
                }
            }

            return redirect('https://minimarket.nexposolution.com' . $request->getRequestUri());
        }

        return $next($request);
    }
}
