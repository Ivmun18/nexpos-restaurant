<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaVenta;
use App\Models\OpticaProducto;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OpticaReportesController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', Carbon::today()->toDateString());

        $resumen = OpticaVenta::where('empresa_id',$empresa_id)
            ->where('estado','pagado')
            ->whereBetween('fecha',[$desde,$hasta])
            ->selectRaw('COUNT(*) as total_ventas, SUM(total) as ingresos, SUM(igv) as total_igv')
            ->first();

        $porMetodo = OpticaVenta::where('empresa_id',$empresa_id)
            ->where('estado','pagado')->whereBetween('fecha',[$desde,$hasta])
            ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('metodo_pago')->get();

        $porDia = OpticaVenta::where('empresa_id',$empresa_id)
            ->where('estado','pagado')->whereBetween('fecha',[$desde,$hasta])
            ->selectRaw('DATE(fecha) as dia, SUM(total) as total, COUNT(*) as cantidad')
            ->groupBy('dia')->orderBy('dia')->get();

        $topProductos = DB::table('optica_venta_items')
            ->join('optica_ventas','optica_ventas.id','=','optica_venta_items.venta_id')
            ->where('optica_ventas.empresa_id',$empresa_id)
            ->where('optica_ventas.estado','pagado')
            ->whereBetween('optica_ventas.fecha',[$desde,$hasta])
            ->selectRaw('descripcion, SUM(cantidad) as cantidad, SUM(subtotal) as total')
            ->groupBy('descripcion')->orderByDesc('total')->take(10)->get();

        $stockBajo = OpticaProducto::where('empresa_id',$empresa_id)
            ->whereColumn('stock','<=','stock_minimo')->where('activo',true)->get();

        return Inertia::render('Optica/Reportes/Index', compact(
            'resumen','porMetodo','porDia','topProductos','stockBajo','desde','hasta'
        ));
    }

    public function export(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', Carbon::today()->toDateString());
        $ventas = OpticaVenta::where('empresa_id',$empresa_id)
            ->where('estado','pagado')->whereBetween('fecha',[$desde,$hasta])
            ->with('paciente')->get();

        $csv = "Numero,Fecha,Paciente,Total,Metodo Pago,Comprobante\n";
        foreach ($ventas as $v) {
            $csv .= implode(',', [
                $v->numero_venta, $v->fecha, $v->paciente?->nombre_completo ?? 'Sin paciente',
                $v->total, $v->metodo_pago, $v->tipo_comprobante
            ]) . "\n";
        }
        return response($csv, 200, [
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>'attachment; filename="ventas-optica-'.$desde.'-'.$hasta.'.csv"'
        ]);
    }
}
