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
        return response()->json(['success' => true, 'saldo' => max(0, $saldo), 'pago_id' => $pago->id]);
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


    public function ticketAdelanto($pagoId) {
        $empresaId = $this->empresaId();
        $pago = \DB::table('odonto_pagos')->where('id', $pagoId)->where('empresa_id', $empresaId)->first();
        if (!$pago) abort(404);
        $paciente = \DB::table('odonto_pacientes')->where('id', $pago->paciente_id)->first();
        $presupuesto = $pago->presupuesto_id ? \DB::table('odonto_presupuestos')->where('id', $pago->presupuesto_id)->first() : null;
        $cuota = \DB::table('odonto_pago_cuotas')->where('pago_id', $pagoId)->first();
        $empresa = auth()->user()->empresa;
        $totalAdelantado = \DB::table('odonto_pagos')
            ->where('presupuesto_id', $pago->presupuesto_id)
            ->where('tipo_movimiento', 'adelanto')
            ->sum('monto_total');

        $html = "<!DOCTYPE html><html><head><meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 13px; color: #1E293B; margin: 0; padding: 24px; max-width: 400px; }
            .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #8B5CF6; padding-bottom: 16px; }
            .empresa { font-size: 18px; font-weight: 700; color: #8B5CF6; }
            .titulo { font-size: 16px; font-weight: 700; margin: 10px 0 4px; }
            .no-sunat { background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 8px 12px; text-align: center; font-size: 11px; font-weight: 700; color: #92400E; margin: 12px 0; }
            .row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #F1F5F9; font-size: 13px; }
            .row .label { color: #64748B; }
            .monto { font-size: 22px; font-weight: 700; color: #8B5CF6; text-align: center; padding: 12px; background: #F5F3FF; border-radius: 8px; margin: 14px 0; }
            .saldo { font-size: 14px; font-weight: 700; color: #DC2626; text-align: center; }
            .footer { text-align: center; margin-top: 20px; font-size: 11px; color: #94A3B8; border-top: 1px dashed #E2E8F0; padding-top: 12px; }
        </style></head><body>
        <div class='header'>
            <div class='empresa'>🦷 {$empresa->nombre}</div>
            <div style='font-size:11px; color:#64748B; margin-top:4px;'>{$empresa->direccion} · {$empresa->telefono}</div>
            <div class='titulo'>RECIBO DE ADELANTO</div>
            <div style='font-size:12px; color:#64748B;'>N° {$pagoId} · " . now()->format('d/m/Y H:i') . "</div>
        </div>
        <div class='no-sunat'>⚠️ DOCUMENTO INTERNO — NO ES COMPROBANTE SUNAT</div>
        <div class='row'><span class='label'>Paciente</span><span style='font-weight:600;'>" . ($paciente->apellidos ?? '') . ", " . ($paciente->nombres ?? '') . "</span></div>
        <div class='row'><span class='label'>DNI</span><span>" . ($paciente->dni ?? '-') . "</span></div>
        " . ($presupuesto ? "<div class='row'><span class='label'>Presupuesto</span><span>#" . $presupuesto->id . " — S/ " . number_format($presupuesto->total, 2) . "</span></div>" : "") . "
        <div class='row'><span class='label'>Método de pago</span><span style='text-transform:uppercase;'>" . ($cuota->metodo_pago ?? '-') . "</span></div>
        <div class='row'><span class='label'>Fecha</span><span>" . $pago->fecha . "</span></div>
        <div class='monto'>ADELANTO: S/ " . number_format($pago->monto_total, 2) . "</div>
        " . ($presupuesto ? "<div class='saldo'>Saldo pendiente: S/ " . number_format(max(0, $presupuesto->total - $totalAdelantado), 2) . "</div>" : "") . "
        <div class='footer'>
            Gracias por su confianza<br>
            {$empresa->nombre} · " . now()->format('d/m/Y H:i') . "
        </div>
        </body></html>";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper([0, 0, 226, 400], 'portrait');
        return $pdf->stream('adelanto-'.$pagoId.'.pdf');
    }


    public function reciboCobroPDF(Request $request) {
        $empresaId = $this->empresaId();
        $empresa = auth()->user()->empresa;
        $paciente = $request->paciente_id ? \DB::table('odonto_pacientes')->where('id', $request->paciente_id)->first() : null;
        $items = $request->items ?? [];
        $total = collect($items)->sum(fn($i) => $i['monto']);
        $metodo = strtoupper($request->metodo_pago ?? 'EFECTIVO');
        $fecha = now()->format('d/m/Y H:i');
        $numero = 'RC-' . str_pad(rand(1, 9999), 6, '0', STR_PAD_LEFT);

        $itemsHtml = collect($items)->map(fn($i) => "
            <tr>
                <td style='padding:6px 8px; border-bottom:1px solid #F1F5F9; font-size:12px;'>{$i['descripcion']}</td>
                <td style='padding:6px 8px; border-bottom:1px solid #F1F5F9; text-align:right; font-size:12px; font-weight:600;'>S/ " . number_format($i['monto'], 2) . "</td>
            </tr>")->implode('');

        $pacienteHtml = $paciente
            ? "<div class='row'><span class='label'>Paciente</span><span style='font-weight:600;'>{$paciente->apellidos}, {$paciente->nombres}</span></div>
               <div class='row'><span class='label'>DNI</span><span>{$paciente->dni}</span></div>"
            : "<div class='row'><span class='label'>Paciente</span><span>Cliente general</span></div>";

        $html = "<!DOCTYPE html><html><head><meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 13px; color: #1E293B; margin: 0; padding: 24px; max-width: 400px; }
            .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #8B5CF6; padding-bottom: 14px; }
            .empresa { font-size: 18px; font-weight: 700; color: #8B5CF6; }
            .titulo { font-size: 15px; font-weight: 700; margin: 8px 0 2px; }
            .interno { background:#EDE9FE; border-radius:6px; padding:6px 12px; text-align:center; font-size:11px; font-weight:700; color:#5B21B6; margin:10px 0; }
            .row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #F1F5F9; font-size:12px; }
            .label { color:#64748B; }
            table { width:100%; border-collapse:collapse; margin:12px 0; }
            th { background:#F8FAFC; padding:8px; text-align:left; font-size:11px; color:#64748B; font-weight:600; }
            .metodo { display:inline-block; background:#EDE9FE; color:#5B21B6; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
            .total { font-size:20px; font-weight:700; color:#8B5CF6; text-align:center; padding:12px; background:#F5F3FF; border-radius:8px; margin:12px 0; }
            .footer { text-align:center; margin-top:16px; font-size:11px; color:#94A3B8; border-top:1px dashed #E2E8F0; padding-top:10px; }
        </style></head><body>
        <div class='header'>
            <div class='empresa'>🦷 {$empresa->nombre}</div>
            <div style='font-size:11px; color:#64748B; margin-top:3px;'>{$empresa->direccion} · {$empresa->telefono}</div>
            <div class='titulo'>RECIBO DE PAGO</div>
            <div style='font-size:11px; color:#64748B;'>{$numero} · {$fecha}</div>
        </div>
        <div class='interno'>📋 DOCUMENTO INTERNO — NO ES COMPROBANTE SUNAT</div>
        {$pacienteHtml}
        <div class='row'><span class='label'>Método de pago</span><span class='metodo'>{$metodo}</span></div>
        <table>
            <thead><tr><th>Descripción</th><th style='text-align:right;'>Monto</th></tr></thead>
            <tbody>{$itemsHtml}</tbody>
        </table>
        <div class='total'>TOTAL COBRADO: S/ " . number_format($total, 2) . "</div>
        <div class='footer'>Gracias por su pago · {$empresa->nombre}<br>{$fecha}</div>
        </body></html>";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper([0,0,226,500],'portrait');
        return $pdf->stream('recibo-cobro.pdf');
    }

}
