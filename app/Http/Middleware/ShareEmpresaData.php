<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShareEmpresaData
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->empresa) {
            $empresa = auth()->user()->empresa;
            $modules = $empresa->modules_enabled;
            
            // Asegurar que modules_enabled sea array
            if (is_string($modules)) {
                $modules = json_decode($modules, true) ?? [];
            }
            if (!is_array($modules)) {
                $modules = [];
            }

            Inertia::share('empresa', [
                'id'              => $empresa->id,
                'nombre'          => $empresa->nombre_comercial,
                'industry_type'   => $empresa->industry_type,
                'modules_enabled' => $modules,
            ]);
        }

        return $next($request);
    }
}