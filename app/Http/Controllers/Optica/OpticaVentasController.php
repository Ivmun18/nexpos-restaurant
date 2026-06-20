<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaVenta;
use App\Models\OpticaVentaItem;
use App\Models\OpticaProducto;
use App\Models\OpticaPaciente;
use App\Models\OpticaCaja;
use App\Models\OpticaCajaMovimiento;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OpticaVentasController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $ventas = OpticaVenta::where('empresa_id',$empresa_id)
            ->with('paciente')->latest()->paginate(20);
        return Inertia::render('Optica/Ventas/Index', compact('ventas'));
    }

    public function pos(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $productos = OpticaProducto::where('empresa_id',$empresa_id)->where('activo',true)->where('stock','>',0)->get();
        $pacientes = OpticaPaciente::where('empresa_id',$empresa_id)->orderBy('apellidos')->get(['id','nombre','apellidos','dni']);
        $cajaAbierta = OpticaCaja::where('empresa_id',$empresa_id)->where('estado','abierta')->latest()->first();
        return Inertia::render('Optica/Ventas/POS', compact('productos','pacientes','cajaAbierta'));
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $data = $request->validate([
            'paciente_id'=>'nullable|integer',
            'tipo_comprobante'=>'required|in:boleta,factura,ticket',
            'metodo_pago'=>'required|in:efectivo,tarjeta,yape,plin,transferencia',
            'monto_pagado'=>'required|numeric|min:0',
            'ruc_cliente'=>'nullable|string',
            'razon_social_cliente'=>'nullable|string',
            'items'=>'required|array|min:1',
            'items.*.producto_id'=>'nullable|integer',
            'items.*.descripcion'=>'required|string',
            'items.*.cantidad'=>'required|integer|min:1',
            'items.*.precio_unitario'=>'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $cajaAbierta = OpticaCaja::where('empresa_id',$empresa_id)->where('estado','abierta')->latest()->first();

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['cantidad'] * $item['precio_unitario'];
            }
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);
            $vuelto = max(0, $data['monto_pagado'] - $total);

            $count = OpticaVenta::where('empresa_id',$empresa_id)->count();
            $venta = OpticaVenta::create([
                'empresa_id'       => $empresa_id,
                'paciente_id'      => $data['paciente_id'] ?? null,
                'caja_id'          => $cajaAbierta?->id,
                'user_id'          => auth()->id(),
                'numero_venta'     => 'VTA-'.str_pad($count+1,6,'0',STR_PAD_LEFT),
                'fecha'            => Carbon::today(),
                'subtotal'         => round($subtotal / 1.18, 2),
                'igv'              => round($subtotal - $subtotal / 1.18, 2),
                'total'            => $subtotal,
                'monto_pagado'     => $data['monto_pagado'],
                'vuelto'           => $vuelto,
                'metodo_pago'      => $data['metodo_pago'],
                'tipo_comprobante' => $data['tipo_comprobante'],
                'ruc_cliente'      => $data['ruc_cliente'] ?? null,
                'razon_social_cliente' => $data['razon_social_cliente'] ?? null,
                'estado'           => 'pagado',
            ]);

            foreach ($data['items'] as $item) {
                OpticaVentaItem::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'descripcion'     => $item['descripcion'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal'        => $item['cantidad'] * $item['precio_unitario'],
                ]);
                if (!empty($item['producto_id'])) {
                    OpticaProducto::where('id',$item['producto_id'])->decrement('stock', $item['cantidad']);
                }
            }

            if ($cajaAbierta) {
                OpticaCajaMovimiento::create([
                    'caja_id'    => $cajaAbierta->id,
                    'empresa_id' => $empresa_id,
                    'tipo'       => 'ingreso',
                    'concepto'   => 'Venta '.$venta->numero_venta,
                    'monto'      => $subtotal,
                    'referencia' => (string)$venta->id,
                ]);
                $cajaAbierta->increment('total_ventas', $subtotal);
            }

            DB::commit();
            return response()->json(['success'=>true,'venta_id'=>$venta->id,'numero'=>$venta->numero_venta,'vuelto'=>$vuelto]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    public function comprobante($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $venta = OpticaVenta::where('empresa_id',$empresa_id)->with('paciente','items.producto')->findOrFail($id);
        $empresa = DB::table('empresas')->where('id',$empresa_id)->first();
        $pdf = Pdf::loadView('optica.comprobante-pdf', compact('venta','empresa'))->setPaper([0,0,226.77,400],'portrait');
        return $pdf->stream('comprobante-'.$venta->numero_venta.'.pdf');
    }

    public function anular($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $venta = OpticaVenta::where('empresa_id',$empresa_id)->findOrFail($id);
        $venta->update(['estado'=>'anulado']);
        return back()->with('success','Venta anulada.');
    }
}
