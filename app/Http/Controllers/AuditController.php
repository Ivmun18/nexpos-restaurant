<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    /**
     * Mostrar listado de auditorías con filtros
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('usuario');

        // Filtros
        if ($request->modulo) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->accion) {
            $query->where('accion', $request->accion);
        }

        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->buscar) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('detalles', 'like', "%{$buscar}%")
                    ->orWhere('ip_address', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', function ($u) use ($buscar) {
                        $u->where('name', 'like', "%{$buscar}%")
                            ->orWhere('email', 'like', "%{$buscar}%");
                    });
            });
        }

        // Obtener auditorías paginadas
        $audits = $query->orderBy('created_at', 'desc')->paginate(20);

        // Obtener opciones para filtros (select boxes)
        $modulos = AuditLog::distinct('modulo')->pluck('modulo')->sort()->values();
        $acciones = AuditLog::distinct('accion')->pluck('accion')->sort()->values();
        $usuarios = User::where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Estadísticas
        $estadisticas = [
            'total_registros' => AuditLog::count(),
            'hoy' => AuditLog::whereDate('created_at', today())->count(),
            'esta_semana' => AuditLog::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'este_mes' => AuditLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return Inertia::render('Admin/Auditoria/Index', [
            'audits' => $audits,
            'modulos' => $modulos,
            'acciones' => $acciones,
            'usuarios' => $usuarios,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'modulo' => $request->modulo,
                'usuario_id' => $request->usuario_id,
                'accion' => $request->accion,
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta,
                'buscar' => $request->buscar,
            ],
        ]);
    }

    /**
     * Ver detalles completos de un registro de auditoría
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('usuario');

        return Inertia::render('Admin/Auditoria/Show', [
            'auditLog' => $auditLog,
        ]);
    }

    /**
     * Exportar auditorías a Excel
     */
    public function exportar(Request $request)
    {
        $query = AuditLog::with('usuario');

        // Aplicar los mismos filtros
        if ($request->modulo) {
            $query->where('modulo', $request->modulo);
        }
        if ($request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }
        if ($request->accion) {
            $query->where('accion', $request->accion);
        }
        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $audits = $query->orderBy('created_at', 'desc')->get();

        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Auditoría');

        // Encabezados
        $headers = ['Fecha', 'Hora', 'Usuario', 'Email', 'Acción', 'Módulo', 'Registro ID', 'IP Address', 'Detalles'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header);
            $sheet->getStyle(chr(65 + $key) . '1')->getFont()->setBold(true);
            $sheet->getStyle(chr(65 + $key) . '1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
        }

        // Datos
        $row = 2;
        foreach ($audits as $audit) {
            $sheet->setCellValue("A{$row}", $audit->created_at->format('Y-m-d'));
            $sheet->setCellValue("B{$row}", $audit->created_at->format('H:i:s'));
            $sheet->setCellValue("C{$row}", $audit->usuario->name ?? 'Sistema');
            $sheet->setCellValue("D{$row}", $audit->usuario->email ?? '');
            $sheet->setCellValue("E{$row}", $audit->accion);
            $sheet->setCellValue("F{$row}", $audit->modulo);
            $sheet->setCellValue("G{$row}", $audit->registro_id);
            $sheet->setCellValue("H{$row}", $audit->ip_address);
            $sheet->setCellValue("I{$row}", $audit->detalles);
            $row++;
        }

        // Ajustar ancho de columnas
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Descargar
        $filename = 'auditoria_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return response()->stream(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]
        );
    }

    /**
     * Limpiar registros de auditoría antiguos
     */
    public function limpiar(Request $request)
    {
        $request->validate([
            'dias' => 'required|integer|min:1|max:365',
        ]);

        $fecha_limite = now()->subDays($request->dias);
        $eliminados = AuditLog::where('created_at', '<', $fecha_limite)->delete();

        return back()->with('success', "Se eliminaron {$eliminados} registros de auditoría anteriores a {$fecha_limite->format('Y-m-d')}");
    }
}
