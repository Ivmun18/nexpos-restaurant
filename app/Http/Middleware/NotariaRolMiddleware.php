<?php

namespace App\Http\Middleware;

use App\Models\ActoNotarial;
use App\Models\ActoRequisito;
use Closure;
use Illuminate\Http\Request;

class NotariaRolMiddleware
{
    // Prefijos/nombres de ruta del área de Expedientes (Certificaciones y Legalizaciones)
    const PREFIJOS_ASISTENTE = [
        'notaria.actos.',
        'notaria.seguimiento',
        'notaria.recibo.',
        'notaria.requisitos.',
        'notaria.clientes.',
        'notaria.comprobantes.emitir',
    ];

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

        // Notario: todo EXCEPTO caja
        if ($rol === 'notario') {
            if (in_array($rutaActual, self::RUTAS_CAJA)) {
                return redirect('/dashboard')->with('error', 'No tienes acceso a Caja.');
            }
            return $next($request);
        }

        // Abogado/Asistente: solo Certificaciones y Legalizaciones
        if ($rol === 'asistente') {
            $permitido = $rutaActual === 'dashboard'
                || collect(self::PREFIJOS_ASISTENTE)->contains(
                    fn ($prefijo) => $rutaActual === $prefijo || str_starts_with((string) $rutaActual, $prefijo)
                );

            if (!$permitido) {
                return redirect('/dashboard')->with('error', 'Tu acceso está limitado a Certificaciones y Legalizaciones.');
            }

            $acto = $request->route('acto');
            if ($acto instanceof ActoNotarial && $acto->tipo_acto !== 'legalizacion') {
                abort(403, 'Solo tienes acceso a expedientes de legalización.');
            }

            $requisito = $request->route('requisito');
            if ($requisito instanceof ActoRequisito && $requisito->acto?->tipo_acto !== 'legalizacion') {
                abort(403, 'Solo tienes acceso a expedientes de legalización.');
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
