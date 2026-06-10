<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPago;
use App\Models\Odontologia\OdontoPagoCuota;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoPaciente;

class PagoController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $pagos = OdontoPago::with(['paciente','presupuesto','cuotas'])
            ->where('empresa_id', $empresaId)
            ->orderByDesc('fecha')
            ->paginate(20);
        $doctores = OdontoDoctor::where('empresa_id', $empresaId)->where('activo', true)->get(['id','nombre']);
        $pacientes = OdontoPaciente::where('empresa_id', $empresaId)->where('activo', true)->get(['id','nombres','apellidos','dni']);
        return Inertia::render('Odontologia/Pagos/Index', compact('pagos','doctores','pacientes'));
    }

    public function store(Request $request) {
        $request->validate([
            'paciente_id'  => 'required|exists:odonto_pacientes,id',
            'monto_total'  => 'required|numeric|min:0.01',
            'tipo_pago'    => 'required|in:contado,cuotas',
            'num_cuotas'   => 'required_if:tipo_pago,cuotas|integer|min:1',
        ]);

        $pago = OdontoPago::create([
            'empresa_id'    => $this->empresaId(),
            'paciente_id'   => $request->paciente_id,
            'presupuesto_id'=> $request->presupuesto_id,
            'fecha'         => now()->toDateString(),
            'monto_total'   => $request->monto_total,
            'num_cuotas'    => $request->tipo_pago === 'contado' ? 1 : $request->num_cuotas,
            'tipo_pago'     => $request->tipo_pago,
            'estado'        => 'pendiente',
            'observaciones' => $request->observaciones,
        ]);

        if ($request->tipo_pago === 'contado') {
            OdontoPagoCuota::create([
                'pago_id'          => $pago->id,
                'numero_cuota'     => 1,
                'monto'            => $request->monto_total,
                'fecha_vencimiento'=> now()->toDateString(),
                'estado'           => 'pendiente',
            ]);
        } else {
            $montoCuota = round($request->monto_total / $request->num_cuotas, 2);
            for ($i = 1; $i <= $request->num_cuotas; $i++) {
                OdontoPagoCuota::create([
                    'pago_id'          => $pago->id,
                    'numero_cuota'     => $i,
                    'monto'            => $i == $request->num_cuotas
                        ? $request->monto_total - ($montoCuota * ($request->num_cuotas - 1))
                        : $montoCuota,
                    'fecha_vencimiento'=> now()->addMonths($i - 1)->toDateString(),
                    'estado'           => 'pendiente',
                ]);
            }
        }

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function pagarCuota(Request $request, $cuotaId) {
        $cuota = OdontoPagoCuota::findOrFail($cuotaId);
        $request->validate([
            'metodo_pago' => 'required|in:efectivo,yape,plin,tarjeta,transferencia',
        ]);

        $cuota->update([
            'fecha_pago'  => now()->toDateString(),
            'metodo_pago' => $request->metodo_pago,
            'estado'      => 'pagado',
        ]);

        $pago = OdontoPago::find($cuota->pago_id);
        $todasPagadas = OdontoPagoCuota::where('pago_id', $pago->id)->where('estado', '!=', 'pagado')->count() === 0;
        $pago->update(['estado' => $todasPagadas ? 'pagado' : 'parcial']);

        return back()->with('success', 'Cuota pagada correctamente.');
    }

    public function caja(Request $request) {
        $empresaId = $this->empresaId();
        $pacientes = OdontoPaciente::where('empresa_id', $empresaId)->where('activo', true)->get(['id','nombres','apellidos','dni','telefono']);
        return Inertia::render('Odontologia/Caja/Index', compact('pacientes'));
    }

    public function presupuestosPaciente($pacienteId) {
        $empresaId = $this->empresaId();
        $presupuestos = \DB::table('odonto_presupuestos')
            ->where('empresa_id', $empresaId)
            ->where('paciente_id', $pacienteId)
            ->whereIn('estado', ['borrador','enviado','aprobado'])
            ->get();
        foreach ($presupuestos as $p) {
            $p->items = \DB::table('odonto_presupuesto_items')->where('presupuesto_id', $p->id)->get();
            $p->cuotas = \DB::table('odonto_pago_cuotas')
                ->join('odonto_pagos','odonto_pagos.id','=','odonto_pago_cuotas.pago_id')
                ->where('odonto_pagos.presupuesto_id', $p->id)
                ->where('odonto_pago_cuotas.estado','!=','pagado')
                ->select('odonto_pago_cuotas.*')
                ->get();
            $totalAdelantado = \DB::table('odonto_pagos')
                ->where('presupuesto_id', $p->id)
                ->where('tipo_movimiento', 'adelanto')
                ->sum('monto_total');
            $p->total_adelantado = $totalAdelantado;
            $p->saldo_pendiente = max(0, $p->total - $totalAdelantado);
        }
        return response()->json($presupuestos);
    }

    public function registrarAdelanto(Request $request) {
        $request->validate([
            'paciente_id'    => 'required|exists:odonto_pacientes,id',
            'presupuesto_id' => 'required|exists:odonto_presupuestos,id',
            'monto'          => 'required|numeric|min:0.01',
            'metodo_pago'    => 'required|in:efectivo,yape,plin,tarjeta,transferencia',
        ]);
        $empresaId = $this->empresaId();
        $presupuesto = \DB::table('odonto_presupuestos')->where('id', $request->presupuesto_id)->first();
        $totalAdelantado = \DB::table('odonto_pagos')
            ->where('presupuesto_id', $request->presupuesto_id)
            ->where('tipo_movimiento', 'adelanto')
            ->sum('monto_total');
        $saldo = $presupuesto->total - $totalAdelantado - $request->monto;

        $pago = OdontoPago::create([
            'empresa_id'      => $empresaId,
            'paciente_id'     => $request->paciente_id,
            'presupuesto_id'  => $request->presupuesto_id,
            'fecha'           => now()->toDateString(),
            'monto_total'     => $request->monto,
            'saldo_pendiente' => max(0, $saldo),
            'num_cuotas'      => 1,
            'tipo_pago'       => 'contado',
            'tipo_movimiento' => 'adelanto',
            'estado'          => 'pagado',
            'observaciones'   => 'Adelanto - ' . $request->metodo_pago,
        ]);
        OdontoPagoCuota::create([
            'pago_id'          => $pago->id,
            'numero_cuota'     => 1,
            'monto'            => $request->monto,
            'fecha_vencimiento'=> now()->toDateString(),
            'fecha_pago'       => now()->toDateString(),
            'metodo_pago'      => $request->metodo_pago,
            'estado'           => 'pagado',
        ]);
        return response()->json(['success' => true, 'saldo' => max(0, $saldo)]);
    }

    public function cobroRapido(Request $request) {
        $request->validate([
            'descripcion' => 'required|string',
            'monto'       => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,yape,plin,tarjeta,transferencia',
        ]);
        $empresaId = $this->empresaId();
        $pago = OdontoPago::create([
            'empresa_id'    => $empresaId,
            'paciente_id'   => $request->paciente_id ?? null,
            'fecha'         => now()->toDateString(),
            'monto_total'   => $request->monto,
            'num_cuotas'    => 1,
            'tipo_pago'     => 'contado',
            'estado'        => 'pagado',
            'observaciones' => $request->descripcion,
        ]);
        OdontoPagoCuota::create([
            'pago_id'          => $pago->id,
            'numero_cuota'     => 1,
            'monto'            => $request->monto,
            'fecha_vencimiento'=> now()->toDateString(),
            'fecha_pago'       => now()->toDateString(),
            'metodo_pago'      => $request->metodo_pago,
            'estado'           => 'pagado',
        ]);
        return back()->with('success', 'Cobro registrado correctamente.');
    }

}
