<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CuentasCobrarController extends Controller
{
    public function index(Request $request)
    {
        $empresa = auth()->user()->empresa;

        $facturas = DB::table('comprobantes_sunat')
            ->where('comprobantes_sunat.empresa_id', $empresa->id)
            ->where('comprobantes_sunat.forma_pago', 'Credito')
            ->where('comprobantes_sunat.tipo_comprobante', '01')
            ->where('comprobantes_sunat.estado', '!=', 'anulado')
            ->when($request->estado_filtro, function ($q) use ($request) {
                if ($request->estado_filtro === 'pendiente') {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))->from('cuotas_credito')
                            ->whereColumn('cuotas_credito.comprobante_id', 'comprobantes_sunat.id')
                            ->where('cuotas_credito.estado', 'pendiente');
                    });
                } elseif ($request->estado_filtro === 'pagado') {
                    $q->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))->from('cuotas_credito')
                            ->whereColumn('cuotas_credito.comprobante_id', 'comprobantes_sunat.id')
                            ->where('cuotas_credito.estado', 'pendiente');
                    });
                }
            })
            ->when($request->buscar, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('cliente_nombre', 'like', '%' . $request->buscar . '%')
                       ->orWhere('cliente_numero_documento', 'like', '%' . $request->buscar . '%')
                       ->orWhere(DB::raw("CONCAT(serie, '-', LPAD(numero, 8, '0'))"), 'like', '%' . $request->buscar . '%');
                });
            })
            ->orderByDesc('fecha_emision')
            ->paginate(20);

        // Agregar cuotas a cada factura
        $ids = collect($facturas->items())->pluck('id');
        $cuotas = DB::table('cuotas_credito')->whereIn('comprobante_id', $ids)->orderBy('numero_cuota')->get()->groupBy('comprobante_id');

        $items = collect($facturas->items())->map(function ($f) use ($cuotas) {
            $f->cuotas = $cuotas->get($f->id, collect())->values();
            $f->total_pagado = $cuotas->get($f->id, collect())->sum('monto_pagado');
            $f->total_pendiente = $f->total - $f->total_pagado;
            $f->documento = $f->serie . '-' . str_pad($f->numero, 8, '0', STR_PAD_LEFT);
            return $f;
        });

        return Inertia::render('Notaria/CuentasCobrar/Index', [
            'facturas'     => $facturas,
            'items'        => $items,
            'filtros'      => $request->only('estado_filtro', 'buscar'),
        ]);
    }

    public function registrarPago(Request $request, $cuotaId)
    {
        $request->validate([
            'monto'       => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'referencia'  => 'nullable|string',
        ]);

        $empresa = auth()->user()->empresa;
        $cuota = DB::table('cuotas_credito')->where('id', $cuotaId)->where('empresa_id', $empresa->id)->first();

        if (!$cuota || $cuota->estado === 'pagada') {
            return back()->with('error', 'Cuota no encontrada o ya pagada');
        }

        DB::table('cuotas_credito')->where('id', $cuotaId)->update([
            'monto_pagado' => $request->monto,
            'fecha_pago'   => now()->toDateString(),
            'estado'       => 'pagada',
            'metodo_pago'  => $request->metodo_pago,
            'referencia'   => $request->referencia,
            'updated_at'   => now(),
        ]);

        // Registrar ingreso en caja si hay sesión abierta
        $sesion = DB::table('sesiones_caja')
            ->join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')
            ->where('sesiones_caja.estado', 'abierta')
            ->where('caja.empresa_id', $empresa->id)
            ->select('sesiones_caja.*')->first();

        if ($sesion) {
            $comp = DB::table('comprobantes_sunat')->find($cuota->comprobante_id);
            DB::table('caja_movimientos')->insert([
                'sesion_id'  => $sesion->id,
                'usuario_id' => auth()->id(),
                'tipo'       => 'ingreso',
                'concepto'   => 'Pago cuota ' . $cuota->numero_cuota . ' - ' . ($comp->serie ?? '') . '-' . str_pad($comp->numero ?? 0, 8, '0', STR_PAD_LEFT),
                'monto'      => $request->monto,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Pago registrado correctamente');
    }
}
