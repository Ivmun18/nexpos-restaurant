<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // Filtros
        $query = AuditoriaLog::with('usuario:id,name')
            ->where('empresa_id', $empresaId);

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        if ($request->filled('severidad')) {
            $query->where('severidad', $request->severidad);
        }
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('descripcion', 'LIKE', "%{$q}%")
                    ->orWhere('entidad_descripcion', 'LIKE', "%{$q}%")
                    ->orWhere('usuario_nombre', 'LIKE', "%{$q}%");
            });
        }

        $eventos = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Estadísticas rápidas
        $hoy = AuditoriaLog::where('empresa_id', $empresaId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $criticosSemana = AuditoriaLog::where('empresa_id', $empresaId)
            ->where('severidad', 'critical')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $usuariosActivos = AuditoriaLog::where('empresa_id', $empresaId)
            ->whereDate('created_at', now()->toDateString())
            ->distinct('usuario_id')
            ->count('usuario_id');

        // Lista de usuarios para el filtro
        $usuarios = \App\Models\User::where('empresa_id', $empresaId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Farmacia/Auditoria', [
            'eventos'         => $eventos,
            'usuarios'        => $usuarios,
            'filtros'         => $request->only(['categoria', 'severidad', 'usuario_id', 'desde', 'hasta', 'q']),
            'stats'           => [
                'hoy'             => $hoy,
                'criticos_semana' => $criticosSemana,
                'usuarios_activos'=> $usuariosActivos,
                'total'           => AuditoriaLog::where('empresa_id', $empresaId)->count(),
            ],
        ]);
    }

    public function show($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $evento = AuditoriaLog::with('usuario')
            ->where('empresa_id', $empresaId)
            ->findOrFail($id);

        return response()->json($evento);
    }

    /**
     * Exportar a CSV (auditoría para DIGEMID)
     */
    public function exportarCsv(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $query = AuditoriaLog::where('empresa_id', $empresaId);

        if ($request->filled('categoria')) $query->where('categoria', $request->categoria);
        if ($request->filled('desde'))     $query->whereDate('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))     $query->whereDate('created_at', '<=', $request->hasta);

        $eventos = $query->orderBy('created_at', 'desc')->get();

        $filename = 'auditoria_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($eventos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
            fputcsv($file, ['Fecha', 'Usuario', 'Categoría', 'Acción', 'Entidad', 'Descripción', 'Severidad', 'IP']);
            foreach ($eventos as $e) {
                fputcsv($file, [
                    $e->created_at->format('Y-m-d H:i:s'),
                    $e->usuario_nombre,
                    $e->categoria,
                    $e->accion,
                    $e->entidad_descripcion,
                    $e->descripcion,
                    $e->severidad,
                    $e->ip,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
