<?php

namespace App\Http\Controllers\Gimnasio;

use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioPago;
use App\Models\Gimnasio\GimnasioMiembro;
use App\Models\Gimnasio\GimnasioPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->format('Y-m-d'));
        $buscar = $request->get('buscar', '');

        $pagos = GimnasioPago::where('empresa_id', $empresa_id)
            ->whereBetween('fecha_pago', [$desde, $hasta])
            ->when($buscar, fn($q) => $q->whereHas('miembro', fn($q2) =>
                $q2->where('nombre', 'like', "%$buscar%")
                   ->orWhere('apellidos', 'like', "%$buscar%")
                   ->orWhere('dni', 'like', "%$buscar%")
            ))
            ->with('miembro', 'plan')
            ->orderByDesc('fecha_pago')
            ->get();

        $totalEfectivo     = $pagos->where('metodo_pago', 'efectivo')->sum('monto');
        $totalYape         = $pagos->where('metodo_pago', 'yape')->sum('monto');
        $totalTransferencia= $pagos->whereIn('metodo_pago', ['transferencia','plin'])->sum('monto');
        $totalGeneral      = $pagos->sum('monto');

        $planes = GimnasioPlan::where('empresa_id', $empresa_id)->get();

        return Inertia::render('Gimnasio/Pagos/Index', compact(
            'pagos', 'desde', 'hasta', 'buscar',
            'totalEfectivo', 'totalYape', 'totalTransferencia', 'totalGeneral', 'planes'
        ));
    }
}
