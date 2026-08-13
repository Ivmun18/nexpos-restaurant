<?php

namespace App\Http\Middleware;

use App\Models\ActoNotarial;
use App\Models\ActoRequisito;
use Closure;
use Illuminate\Http\Request;

class NotariaRolMiddleware
{
    // Prefijos/nombres de ruta del área de Expedientes, compartidos por los
    // roles restringidos por tipo_acto (ver User::TIPOS_ACTO_POR_ROL).
    const PREFIJOS_EXPEDIENTES = [
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

        // Roles restringidos a un subconjunto de tipo_acto dentro de Expedientes
        // (asistente, prescripciones, legalizaciones, notificaciones, mixto)
        $tiposPermitidos = $user->tipoActoPermitidos();
        if ($tiposPermitidos !== null) {
            $permitido = $rutaActual === 'dashboard'
                || collect(self::PREFIJOS_EXPEDIENTES)->contains(
                    fn ($prefijo) => $rutaActual === $prefijo || str_starts_with((string) $rutaActual, $prefijo)
                );

            if (!$permitido) {
                return redirect('/dashboard')->with('error', 'Tu acceso está limitado a tu área de trabajo.');
            }

            $acto = $request->route('acto');
            if ($acto instanceof ActoNotarial && !in_array($acto->tipo_acto, $tiposPermitidos, true)) {
                abort(403, 'No tienes acceso a este tipo de expediente.');
            }

            $requisito = $request->route('requisito');
            if ($requisito instanceof ActoRequisito && !in_array($requisito->acto?->tipo_acto, $tiposPermitidos, true)) {
                abort(403, 'No tienes acceso a este tipo de expediente.');
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
