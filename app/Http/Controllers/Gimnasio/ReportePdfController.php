<?php

namespace App\Http\Controllers\Gimnasio;

use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioPago;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportePdfController extends Controller
{
    public function contable(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $empresa    = auth()->user()->empresa;
        $desde      = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta      = $request->get('hasta', Carbon::now()->format('Y-m-d'));

        $pagos = GimnasioPago::where('empresa_id', $empresa_id)
            ->whereBetween('fecha_pago', [$desde, $hasta])
            ->where('estado', 'pagado')
            ->with('miembro', 'plan')
            ->orderBy('fecha_pago')
            ->get();

        $totalGeneral      = $pagos->sum('monto');
        $totalEfectivo     = $pagos->where('metodo_pago', 'efectivo')->sum('monto');
        $totalYape         = $pagos->whereIn('metodo_pago', ['yape','plin'])->sum('monto');
        $totalTransferencia= $pagos->where('metodo_pago', 'transferencia')->sum('monto');
        $totalSesiones     = $pagos->whereNull('plan_id')->sum('monto');
        $totalMembresias   = $pagos->whereNotNull('plan_id')->sum('monto');

        $html = view('gimnasio.reporte-contable', compact(
            'empresa', 'pagos', 'desde', 'hasta',
            'totalGeneral', 'totalEfectivo', 'totalYape', 'totalTransferencia',
            'totalSesiones', 'totalMembresias'
        ))->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        $filename = 'reporte-contable-' . $desde . '-al-' . $hasta . '.pdf';
        return $pdf->download($filename);
    }
}
