<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPago;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoPaciente;
use App\Models\Odontologia\OdontoPresupuesto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReporteController extends Controller {
    private function empresaId() { return auth()->user()->empresa_id; }

    public function index(Request $request) {
        $eid   = $this->empresaId();
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        // Ingresos por día
        $ingresosDia = OdontoPago::where('empresa_id', $eid)
            ->where('estado', 'pagado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('DATE(fecha) as dia, SUM(monto_total) as total, COUNT(*) as cantidad')
            ->groupBy('dia')->orderBy('dia')->get();

        // Ingresos por tipo de pago
        $ingresosTipo = OdontoPago::where('empresa_id', $eid)
            ->where('estado', 'pagado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('tipo_pago, SUM(monto_total) as total, COUNT(*) as cantidad')
            ->groupBy('tipo_pago')->get();

        // Citas por estado
        $citasEstado = OdontoCita::where('empresa_id', $eid)
            ->whereBetween('fecha_hora', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')->get();

        // Pacientes nuevos
        $pacientesNuevos = OdontoPaciente::where('empresa_id', $eid)
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->count();

        // Totales
        $totalIngresos  = OdontoPago::where('empresa_id', $eid)->where('estado','pagado')->whereBetween('fecha', [$desde, $hasta])->sum('monto_total');
        $totalPendiente = OdontoPago::where('empresa_id', $eid)->where('estado','pendiente')->sum('saldo_pendiente');
        $totalCitas     = OdontoCita::where('empresa_id', $eid)->whereBetween('fecha_hora', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])->count();
        $totalPresupuestos = OdontoPresupuesto::where('empresa_id', $eid)->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])->count();

        return Inertia::render('Odontologia/Reportes/Index', compact(
            'desde','hasta','ingresosDia','ingresosTipo',
            'citasEstado','pacientesNuevos',
            'totalIngresos','totalPendiente','totalCitas','totalPresupuestos'
        ));
    }
}
