<?php
namespace App\Http\Controllers;

use App\Models\CajaRestaurante;
use App\Models\Compra;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteContadorController extends Controller
{
    public function exportarPdf(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());
        $empresaId = auth()->user()->empresa_id;
        $empresa = auth()->user()->empresa;
        $industria = $empresa->industry_type;

        $tiposComp = ['01' => 'Factura', '03' => 'Boleta', '00' => 'S/C', 'ninguno' => 'S/C', '' => 'S/C'];

        // VENTAS según industria
        if ($industria === 'notaria') {
            $ventas = DB::table('comprobantes_sunat')
                ->where('empresa_id', $empresaId)
                ->whereBetween('fecha_emision', [$desde, $hasta])
                ->orderBy('fecha_emision')
                ->get()
                ->map(function($v) {
                    return (object)[
                        'fecha'            => \Carbon\Carbon::parse($v->fecha_emision)->format('d/m/Y'),
                        'comprobante'      => $v->tipo_comprobante,
                        'serie_numero'     => $v->serie . '-' . str_pad($v->numero, 8, '0', STR_PAD_LEFT),
                        'cliente'          => $v->cliente_nombre ?? '-',
                        'subtotal_sin_igv' => (float) $v->total_gravada,
                        'igv'              => (float) $v->total_igv,
                        'total'            => (float) $v->total,
                    ];
                });
        } elseif ($industria === 'restaurante') {
            $ventas = CajaRestaurante::with(['mesa:id,numero,nombre', 'user:id,name'])
                ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                ->orderBy('created_at')
                ->get()
                ->map(function($v) {
                    $total = (float) $v->total;
                    return (object)[
                        'fecha'           => $v->created_at->format('d/m/Y H:i'),
                        'comprobante'     => $v->tipo_comprobante ?? 'ninguno',
                        'serie_numero'    => '-',
                        'cliente'         => '-',
                        'subtotal_sin_igv'=> round($total / 1.18, 2),
                        'igv'             => round($total - round($total / 1.18, 2), 2),
                        'total'           => $total,
                    ];
                });
        } else {
            $ventas = DB::table('ventas')
                ->where('empresa_id', $empresaId)
                ->whereBetween('fecha_emision', [$desde, $hasta])
                ->where('estado', '!=', 'anulado')
                ->orderBy('fecha_emision')
                ->get()
                ->map(function($v) {
                    return (object)[
                        'fecha'           => \Carbon\Carbon::parse($v->fecha_emision)->format('d/m/Y'),
                        'comprobante'     => $v->tipo_comprobante,
                        'serie_numero'    => $v->numero_completo ?? ($v->serie . '-' . str_pad($v->correlativo, 8, '0', STR_PAD_LEFT)),
                        'cliente'         => $v->cliente_razon_social ?? '-',
                        'subtotal_sin_igv'=> (float) $v->total_gravado,
                        'igv'             => (float) $v->total_igv,
                        'total'           => (float) $v->total,
                    ];
                });
        }

        $totalVentas    = $ventas->sum('total');
        $totalIgvVentas = $ventas->sum('igv');
        $totalSubVentas = $ventas->sum('subtotal_sin_igv');

        // COMPRAS (igual para todas las industrias)
        $compras = Compra::with(['proveedor'])
            ->where('empresa_id', $empresaId)
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', '!=', 'anulado')
            ->orderBy('fecha_emision')
            ->get();

        $totalCompras        = $compras->sum('total');
        $totalIgvCompras     = $compras->sum('total_igv');
        $totalGravadoCompras = $compras->sum('total_gravado');

        $industrias = [
            'restaurante' => 'Restaurante',
            'farmacia'    => 'Farmacia',
            'minimarket'  => 'Minimarket',
            'ferreteria'  => 'Ferretería',
            'notaria'     => 'Notaría',
            'gimnasio'    => 'Gimnasio',
        ];
        $nombreIndustria = $industrias[$industria] ?? ucfirst($industria);

        $html = view('pdf.reporte-contador', compact(
            'ventas', 'compras', 'desde', 'hasta', 'empresa', 'tiposComp',
            'totalVentas', 'totalIgvVentas', 'totalSubVentas',
            'totalCompras', 'totalIgvCompras', 'totalGravadoCompras',
            'nombreIndustria'
        ))->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        $filename = 'Reporte_' . $nombreIndustria . '_' . $desde . '_a_' . $hasta . '.pdf';
        return $pdf->download($filename);
    }
}
