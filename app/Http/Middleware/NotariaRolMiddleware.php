<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotariaRolMiddleware
{
    // Rutas accesibles SOLO para cajero/admin (NO notario)
    const RUTAS_CAJA = [
        'notaria.caja.index',
        'notaria.caja.cobrar',
        'notaria.caja.abrir',
        'notaria.caja.cerrar',
        'notaria.caja.venta-directa',
        'notaria.comprobantes.reenviar',
        'notaria.reportes.index',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) return redirect('/login');

        // Solo aplica a empresa de tipo notaria
        $industryType = $user->empresa->industry_type ?? '';
        if ($industryType !== 'notaria') {
            return $next($request);
        }

        $rol = $user->rol;

        // Admin tiene acceso a todo
        if ($rol === 'admin' || $rol === 'superadmin') {
            return $next($request);
        }

        $rutaActual = $request->route()?->getName();

        // Cajero: solo rutas de caja
        if ($rol === 'cajero') {
            if (in_array($rutaActual, self::RUTAS_CAJA)) {
                return $next($request);
            }
            return redirect('/dashboard')->with('error', 'La cajera solo tiene acceso a Caja.');
        }

        // Notario y asistente: todo EXCEPTO caja
        if ($rol === 'notario' || $rol === 'asistente') {
            if (in_array($rutaActual, self::RUTAS_CAJA)) {
                return redirect('/dashboard')->with('error', 'No tienes acceso a Caja.');
            }
            return $next($request);
        }

        // Secretaria: expedientes, clientes, seguimiento — sin caja ni configuración
        if ($rol === 'secretaria') {
            $rutasSecretaria = [
                'notaria.actos.index', 'notaria.actos.show', 'notaria.actos.store',
                'notaria.actos.update', 'notaria.seguimiento.index',
                'notaria.clientes.index', 'notaria.clientes.show',
                'notaria.clientes.store', 'dashboard',
            ];
            if (in_array($rutaActual, $rutasSecretaria)) {
                return $next($request);
            }
            return redirect('/dashboard')->with('error', 'No tienes acceso a esa sección.');
        }

        return redirect('/dashboard')->with('error', 'No tienes permisos para acceder a esa sección.');
    }
}
