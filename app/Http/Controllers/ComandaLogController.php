<?php

namespace App\Http\Controllers;

use App\Models\ComandaLog;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComandaLogController extends Controller
{
    public function index(Request $request): Response
    {
        $desde       = $request->get('desde', now()->startOfDay()->toDateString());
        $hasta       = $request->get('hasta', now()->toDateString());
        $sucursal_id = $request->get('sucursal_id', '');

        $empresaId = auth()->user()->empresa_id;

        $query = ComandaLog::where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('created_at', 'desc');

        if ($sucursal_id) {
            $query->where('sucursal_id', $sucursal_id);
        }

        $logs = $query->paginate(30)->withQueryString();

        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Reportes/ComandaLog', [
            'logs'       => $logs,
            'sucursales' => $sucursales,
            'filtros'    => [
                'desde'       => $desde,
                'hasta'       => $hasta,
                'sucursal_id' => $sucursal_id,
            ],
        ]);
    }

    /**
     * JSON de las comandas de hoy para una sucursal - usado por la pestana
     * "Cocina" del POS (Pos/Index.vue) para reimprimir tickets ya enviados.
     */
    public function hoy(Request $request)
    {
        $sucursal_id = $request->get('sucursal_id');

        $logs = ComandaLog::where('empresa_id', auth()->user()->empresa_id)
            ->where('sucursal_id', $sucursal_id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'mesa_nombre', 'mozo_nombre', 'items', 'created_at']);

        return response()->json($logs);
    }
}
