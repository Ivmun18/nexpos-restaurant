<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPaciente;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoPago;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OdontologiaController extends Controller
{
    private function empresaId() {
        return auth()->user()->empresa->id;
    }

    public function dashboard() {
        $empresaId = $this->empresaId();
        $hoy = now()->toDateString();
        $mes = now()->month;
        $anio = now()->year;

        $stats = [
            'pacientes_total'   => OdontoPaciente::where('empresa_id', $empresaId)->where('activo', true)->count(),
            'citas_hoy'         => OdontoCita::where('empresa_id', $empresaId)->whereDate('fecha_hora', $hoy)->count(),
            'citas_pendientes'  => OdontoCita::where('empresa_id', $empresaId)->whereDate('fecha_hora', $hoy)->whereIn('estado', ['programada','confirmada'])->count(),
            'ingresos_mes'      => OdontoPago::where('empresa_id', $empresaId)->whereMonth('fecha', $mes)->whereYear('fecha', $anio)->where('estado', '!=', 'anulado')->sum('monto_total'),
            'doctores_activos'  => OdontoDoctor::where('empresa_id', $empresaId)->where('activo', true)->count(),
        ];

        $citas_hoy = OdontoCita::with(['paciente','doctor'])
            ->where('empresa_id', $empresaId)
            ->whereDate('fecha_hora', $hoy)
            ->orderBy('fecha_hora')
            ->get();

        return Inertia::render('Odontologia/Dashboard', compact('stats', 'citas_hoy'));
    }
}
