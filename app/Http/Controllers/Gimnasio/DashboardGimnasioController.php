<?php
namespace App\Http\Controllers\Gimnasio;
use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioMiembro;
use App\Models\Gimnasio\GimnasioPago;
use App\Models\Gimnasio\GimnasioAcceso;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardGimnasioController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $hoy = Carbon::today();

        $stats = [
            'total_miembros'   => GimnasioMiembro::where('empresa_id', $empresa_id)->where('activo', true)->count(),
            'activos'          => GimnasioMiembro::where('empresa_id', $empresa_id)->where('estado', 'activo')->count(),
            'vencidos'         => GimnasioMiembro::where('empresa_id', $empresa_id)->where('estado', 'vencido')->count(),
            'por_vencer'       => GimnasioMiembro::where('empresa_id', $empresa_id)
                                    ->where('estado', 'activo')
                                    ->whereBetween('membrecia_vencimiento', [$hoy, $hoy->copy()->addDays(7)])
                                    ->count(),
            'ingresos_mes'     => GimnasioPago::where('empresa_id', $empresa_id)
                                    ->whereMonth('fecha_pago', $hoy->month)
                                    ->whereYear('fecha_pago', $hoy->year)
                                    ->where('estado', 'pagado')
                                    ->sum('monto'),
            'accesos_hoy'      => GimnasioAcceso::where('empresa_id', $empresa_id)
                                    ->whereDate('entrada', $hoy)
                                    ->count(),
        ];

        $vencen_pronto = GimnasioMiembro::where('empresa_id', $empresa_id)
            ->where('estado', 'activo')
            ->whereBetween('membrecia_vencimiento', [$hoy, $hoy->copy()->addDays(7)])
            ->with('plan')
            ->orderBy('membrecia_vencimiento')
            ->get();

        return Inertia::render('Gimnasio/Dashboard', compact('stats', 'vencen_pronto'));
    }
}
