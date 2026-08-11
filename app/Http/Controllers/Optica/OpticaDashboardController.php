<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaPaciente;
use App\Models\OpticaVenta;
use App\Models\OpticaProducto;
use App\Models\OpticaCaja;
use Inertia\Inertia;
use Carbon\Carbon;

class OpticaDashboardController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $hoy = Carbon::today();

        $ventasHoy = OpticaVenta::where('empresa_id', $empresa_id)
            ->whereDate('fecha', $hoy)->where('estado','pagado')->sum('total');

        $atencionesMes = OpticaPaciente::where('empresa_id', $empresa_id)
            ->whereMonth('created_at', $hoy->month)->count();

        $stockBajo = OpticaProducto::where('empresa_id', $empresa_id)
            ->whereColumn('stock','<=','stock_minimo')->where('activo',true)->count();

        $cajaAbierta = OpticaCaja::where('empresa_id', $empresa_id)
            ->where('estado','abierta')->latest()->first();

        $ventasSemana = OpticaVenta::where('empresa_id', $empresa_id)
            ->where('estado','pagado')
            ->whereBetween('fecha', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->selectRaw('DATE(fecha) as dia, SUM(total) as total')
            ->groupBy('dia')->orderBy('dia')->get();

        $ultimasVentas = OpticaVenta::where('empresa_id', $empresa_id)
            ->with('paciente')->latest()->take(5)->get();

        return Inertia::render('Optica/Dashboard', compact(
            'ventasHoy','atencionesMes','stockBajo','cajaAbierta','ventasSemana','ultimasVentas'
        ));
    }
}
