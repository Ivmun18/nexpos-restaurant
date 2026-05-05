<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EmpresaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $empresaId = Auth::user()->empresa_id;

            // Guardar empresa_id en sesión para uso global
            session(['empresa_id' => $empresaId]);

            // Cargar empresa en el request para uso en controladores
            $empresa = Empresa::find($empresaId);
            $request->merge(['_empresa' => $empresa]);

            // Compartir con Inertia
            app()->instance('empresa_actual', $empresa);
        }

        return $next($request);
    }
}