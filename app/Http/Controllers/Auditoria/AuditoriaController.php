<?php

namespace App\Http\Controllers\Auditoria;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::where('empresa_id', EmpresaHelper::id())
            ->with('usuario');

        // Filtros
        if ($request->modulo) {
            $query->where('modulo', $request->modulo);
        }
        if ($request->accion) {
            $query->where('accion', $request->accion);
        }
        if ($request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }
        if ($request->desde) {
            $query->where('created_at', '>=', $request->desde);
        }
        if ($request->hasta) {
            $query->where('created_at', '<=', $request->hasta);
        }

        $logs = $query->latest()->paginate(50);

        $industria = auth()->user()->empresa->industry_type ?? 'general';
        $baseUrl = $industria === 'notaria' ? '/notaria/auditoria' : '/gimnasio/auditoria';
        if (!in_array($industria, ['notaria', 'gimnasio'])) {
            $baseUrl = '/auditoria';
        }

        // Módulos según industria
        $modulosPorIndustria = [
            'notaria'   => ['Notaria', 'Facturación', 'Caja', 'Clientes', 'Productos', 'Usuarios'],
            'gimnasio'  => ['Miembros', 'Planes', 'Pagos', 'Accesos', 'Clases', 'Instructores', 'Usuarios'],
            'hotel'     => ['Habitaciones', 'Reservas', 'Huespedes', 'Pagos', 'Housekeeping', 'Usuarios'],
            'farmacia'  => ['Productos', 'Ventas', 'Compras', 'Caja', 'Clientes', 'Usuarios'],
            'restaurante'=> ['Pedidos', 'Caja', 'Productos', 'Clientes', 'Usuarios'],
            'minimarket' => ['Productos', 'Ventas', 'Compras', 'Caja', 'Usuarios'],
            'ferreteria' => ['Productos', 'Ventas', 'Compras', 'Caja', 'Usuarios'],
            'odontologia'=> ['Pacientes', 'Tratamientos', 'Citas', 'Pagos', 'Usuarios'],
            'general'    => ['Ventas', 'Compras', 'Caja', 'Productos', 'Clientes', 'Usuarios'],
        ];

        // También obtener módulos reales de los logs de esta empresa
        $modulosReales = AuditLog::where('empresa_id', EmpresaHelper::id())
            ->distinct()->pluck('modulo')->filter()->sort()->values()->toArray();

        $modulos = count($modulosReales) > 0
            ? $modulosReales
            : ($modulosPorIndustria[$industria] ?? $modulosPorIndustria['general']);

        return Inertia::render('Auditoria/Index', [
            'logs'     => $logs,
            'modulos'  => $modulos,
            'acciones' => ['create', 'update', 'delete', 'view'],
            'baseUrl'  => $baseUrl,
        ]);
    }

    public function show(AuditLog $log)
    {
        // Verificar que el log pertenece a la empresa del usuario
        if ($log->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }
        $log->load('usuario', 'empresa');
        
        return Inertia::render('Auditoria/Show', [
            'log' => $log,
        ]);
    }
}
