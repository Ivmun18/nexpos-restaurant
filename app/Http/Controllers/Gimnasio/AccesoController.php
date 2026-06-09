<?php
namespace App\Http\Controllers\Gimnasio;
use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioAcceso;
use App\Models\Gimnasio\GimnasioMiembro;
use App\Models\Gimnasio\GimnasioPago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AccesoController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $hoy = Carbon::today();

        $accesos_hoy = GimnasioAcceso::where('empresa_id', $empresa_id)
            ->whereDate('entrada', $hoy)
            ->with('miembro')
            ->orderByDesc('entrada')
            ->get();

        $dentro_ahora = GimnasioAcceso::where('empresa_id', $empresa_id)
            ->whereDate('entrada', $hoy)
            ->whereNull('salida')
            ->with('miembro')
            ->orderByDesc('entrada')
            ->get();

        return Inertia::render('Gimnasio/Accesos/Index', compact('accesos_hoy', 'dentro_ahora'));
    }

    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'miembro_id' => 'required|exists:gimnasio_miembros,id',
        ]);

        $empresa_id = auth()->user()->empresa_id;
        $miembro = GimnasioMiembro::findOrFail($request->miembro_id);

        // Verificar membresía — permitir entrada con advertencia si está vencido
        $advertencia = null;
        if ($miembro->estado === 'vencido') {
            $advertencia = '⚠️ Membresía vencida — entrada registrada como visita';
        }

        // Verificar si ya está dentro
        $yaAdentro = GimnasioAcceso::where('empresa_id', $empresa_id)
            ->where('miembro_id', $miembro->id)
            ->whereDate('entrada', Carbon::today())
            ->whereNull('salida')
            ->exists();

        if ($yaAdentro) {
            return back()->with('error', 'El miembro ya está dentro del gimnasio.');
        }

        GimnasioAcceso::create([
            'empresa_id' => $empresa_id,
            'miembro_id' => $miembro->id,
            'entrada'    => Carbon::now(),
            'tipo_acceso'=> $request->tipo_acceso ?? ($miembro->estado === 'vencido' ? 'visita' : 'normal'),
        ]);

        // Si pagó por sesión, registrar pago
        if ($request->monto_sesion > 0) {
            GimnasioPago::create([
                'empresa_id'  => $empresa_id,
                'miembro_id'  => $miembro->id,
                'plan_id'     => null,
                'monto'       => $request->monto_sesion,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'fecha_pago'  => Carbon::today(),
                'periodo_inicio' => Carbon::today(),
                'periodo_fin'    => Carbon::today(),
                'estado'      => 'pagado',
                'usuario_id'  => auth()->id(),
            ]);
        }

        $msg = $advertencia ?? '✅ Entrada registrada para ' . $miembro->nombre . ' ' . $miembro->apellidos;
        return back()->with('success', $msg);
    }

    public function registrarSalida(GimnasioAcceso $acceso)
    {
        $acceso->update(['salida' => Carbon::now()]);
        return back()->with('success', '👋 Salida registrada para ' . $acceso->miembro->nombre);
    }

    public function buscarMiembro(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $q = $request->q;

        $miembros = GimnasioMiembro::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->whereIn('estado', ['activo', 'vencido'])
            ->where(function($query) use ($q) {
                $query->where('nombre', 'like', "%$q%")
                      ->orWhere('apellidos', 'like', "%$q%")
                      ->orWhere('dni', 'like', "%$q%");
            })
            ->with('plan')
            ->limit(10)
            ->get()
            ->map(function($m) {
                $m->nombre_completo = $m->nombre . ' ' . $m->apellidos;
                $m->dias_restantes = $m->membrecia_vencimiento
                    ? Carbon::now()->diffInDays($m->membrecia_vencimiento, false)
                    : null;
                return $m;
            });

        return response()->json($miembros);
    }
}
