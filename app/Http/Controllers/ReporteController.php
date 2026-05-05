<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function ventas(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());
        $tipo  = $request->get('tipo', '');

        $query = Venta::query()
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->where('estado', '!=', 'anulado');

        if ($tipo) {
            $query->where('tipo_comprobante', $tipo);
        }

        $ventas = $query->orderBy('fecha_emision', 'desc')->get();

        $totalGravado   = $ventas->sum('total_gravado');
        $totalExonerado = $ventas->sum('total_exonerado');
        $totalIgv       = $ventas->sum('total_igv');
        $totalVentas    = $ventas->sum('total');
        $totalFacturas  = $ventas->where('tipo_comprobante', '01')->count();
        $totalBoletas   = $ventas->where('tipo_comprobante', '03')->count();

        $ventasPorDia = $ventas->groupBy(function($v) {
            return $v->fecha_emision instanceof \Carbon\Carbon
                ? $v->fecha_emision->toDateString()
                : $v->fecha_emision;
        })->map(function($group, $fecha) {
            return [
                'fecha'    => $fecha,
                'total'    => round($group->sum('total'), 2),
                'cantidad' => $group->count(),
            ];
        })->values();

        return Inertia::render('Reportes/Ventas', [
            'ventas'       => $ventas,
            'ventasPorDia' => $ventasPorDia,
            'filtros'      => [
                'desde' => $desde,
                'hasta' => $hasta,
                'tipo'  => $tipo,
            ],
            'resumen' => [
                'total_gravado'   => round($totalGravado, 2),
                'total_exonerado' => round($totalExonerado, 2),
                'total_igv'       => round($totalIgv, 2),
                'total_ventas'    => round($totalVentas, 2),
                'total_facturas'  => $totalFacturas,
                'total_boletas'   => $totalBoletas,
                'total_docs'      => $ventas->count(),
            ],
        ]);
    }
public function exportarExcel(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());
        $tipo  = $request->get('tipo') ?? '';

        $nombre = "ventas_{$desde}_{$hasta}.xlsx";

        return Excel::download(new VentasExport($desde, $hasta, $tipo), $nombre);
    }
public function exportarPle(Request $request)
    {
        $mes  = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        $empresaId = \App\Helpers\EmpresaHelper::id();
        $empresa   = \App\Models\Empresa::find($empresaId);

        $ventas = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereMonth('fecha_emision', $mes)
            ->whereYear('fecha_emision', $anio)
            ->where('estado', '!=', 'anulado')
            ->get();

        $lineas = [];
        foreach ($ventas as $v) {
            $fecha = $v->fecha_emision instanceof \Carbon\Carbon
                ? $v->fecha_emision->format('d/m/Y')
                : date('d/m/Y', strtotime($v->fecha_emision));

            $tipoDoc = $v->tipo_comprobante;
            $serie   = $v->serie;
            $corr    = str_pad($v->correlativo, 8, '0', STR_PAD_LEFT);

            $lineas[] = implode('|', [
                $anio . str_pad($mes, 2, '0', STR_PAD_LEFT),  // periodo
                'M' . str_pad(count($lineas)+1, 8, '0', STR_PAD_LEFT), // CUO
                'M' . str_pad(count($lineas)+1, 8, '0', STR_PAD_LEFT), // corr
                $fecha,                         // fecha emision
                '',                             // fecha vencimiento
                $tipoDoc,                       // tipo comprobante
                $serie,                         // serie
                '',                             // rango desde
                $corr,                          // numero
                '',                             // rango hasta
                $v->cliente_tipo_doc ?? '0',    // tipo doc cliente
                $v->cliente_num_doc ?? '',      // num doc cliente
                $v->cliente_razon_social ?? 'CLIENTES VARIOS', // razon social
                number_format($v->total_gravado, 2, '.', ''),   // base imponible
                '0.00',                         // desc base imponible
                number_format($v->total_igv, 2, '.', ''),       // IGV
                '0.00',                         // desc IGV
                '0.00',                         // ISC
                number_format($v->total_exonerado, 2, '.', ''), // exonerado
                '0.00',                         // inafecto
                '0.00',                         // ICBPER
                '0.00',                         // otros tributos
                number_format($v->total, 2, '.', ''),           // importe total
                'PEN',                          // moneda
                '1.000',                        // tipo cambio
                '',                             // fecha emision doc ref
                '',                             // tipo doc ref
                '',                             // serie doc ref
                '',                             // num doc ref
                '1',                            // estado
                '',                             // campo libre
            ]);
        }

        $contenido  = implode("\r\n", $lineas);
        $nombreArchivo = $empresa->ruc . '-14-' . $anio . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-00-PL.txt';

        return response($contenido)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
    }
}