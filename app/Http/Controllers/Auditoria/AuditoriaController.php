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
        $baseUrl = $industria === 'notaria' ? '/notaria/auditoria' : '/auditoria';

        return Inertia::render('Auditoria/Index', [
            'logs'     => $logs,
            'modulos'  => ['Notaria', 'Facturación', 'Caja', 'Clientes', 'Ventas', 'Compras', 'Productos', 'Usuarios', 'Empresas', 'Pedidos'],
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
