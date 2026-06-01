<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClienteNotariaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $buscar = $request->get('buscar');

        $clientes = DB::table('clientes as c')
            ->where('c.empresa_id', $empresaId)
            ->leftJoin('actos_notariales as a', 'a.cliente_id', '=', 'c.id')
            ->select(
                'c.id', 'c.razon_social', 'c.numero_documento', 'c.tipo_documento',
                'c.telefono', 'c.email', 'c.direccion', 'c.activo',
                DB::raw('COUNT(a.id) as total_actos'),
                DB::raw('SUM(a.monto_cobrar) as total_facturado'),
                DB::raw('SUM(a.monto_pagado) as total_pagado'),
                DB::raw('MAX(a.fecha_ingreso) as ultimo_acto')
            )
            ->when($buscar, fn($q) => $q->where(function($q) use ($buscar) {
                $q->where('c.razon_social', 'like', "%$buscar%")
                  ->orWhere('c.numero_documento', 'like', "%$buscar%");
            }))
            ->groupBy('c.id', 'c.razon_social', 'c.numero_documento', 'c.tipo_documento',
                'c.telefono', 'c.email', 'c.direccion', 'c.activo')
            ->orderByDesc('ultimo_acto')
            ->paginate(20);

        return Inertia::render('Notaria/Clientes/Index', compact('clientes', 'buscar'));
    }

    public function show($id)
    {
        $empresaId = auth()->user()->empresa_id;

        $cliente = DB::table('clientes')->where('id', $id)->where('empresa_id', $empresaId)->first();
        if (!$cliente) abort(404);

        $actos = DB::table('actos_notariales as a')
            ->where('a.empresa_id', $empresaId)
            ->where('a.cliente_id', $id)
            ->leftJoin('comprobantes_sunat as cs', 'cs.acto_id', '=', 'a.id')
            ->leftJoin('users as u', 'u.id', '=', 'a.usuario_id')
            ->select(
                'a.id', 'a.numero_expediente', 'a.tipo_acto', 'a.asunto',
                'a.fecha_ingreso', 'a.fecha_entrega', 'a.estado', 'a.estado_pago',
                'a.monto_cobrar', 'a.monto_pagado',
                'u.name as usuario',
                'cs.serie', 'cs.numero as num_comprobante',
                'cs.tipo_comprobante', 'cs.estado as estado_comprobante',
                'cs.total as total_comprobante'
            )
            ->orderByDesc('a.fecha_ingreso')
            ->get()
            ->map(function($a) {
                $a->saldo = round($a->monto_cobrar - $a->monto_pagado, 2);
                $a->comprobante = $a->serie ? $a->serie . '-' . str_pad($a->num_comprobante, 8, '0', STR_PAD_LEFT) : null;
                return $a;
            });

        $pagos = DB::table('acto_pagos as p')
            ->join('actos_notariales as a', 'a.id', '=', 'p.acto_id')
            ->where('a.cliente_id', $id)
            ->where('a.empresa_id', $empresaId)
            ->select('p.*', 'a.numero_expediente', 'a.asunto')
            ->orderByDesc('p.created_at')
            ->get();

        $comprobantes = DB::table('comprobantes_sunat')
            ->where('empresa_id', $empresaId)
            ->where(function($q) use ($actos, $cliente) {
                $q->whereIn('acto_id', $actos->pluck('id'))
                  ->orWhere('cliente_numero_documento', $cliente->numero_documento);
            })
            ->orderByDesc('fecha_emision')
            ->get();

        $totalComprobantes = $comprobantes->whereIn('estado', ['emitido','aceptado'])->sum('total');
        $resumen = [
            'total_actos'     => $actos->count(),
            'total_facturado' => $actos->sum('monto_cobrar') + $totalComprobantes,
            'total_pagado'    => $actos->sum('monto_pagado') + $totalComprobantes,
            'saldo_pendiente' => $actos->sum('saldo'),
            'actos_pagados'   => $actos->where('estado_pago', 'pagado')->count(),
        ];

        return Inertia::render('Notaria/Clientes/Show', compact('cliente', 'actos', 'pagos', 'comprobantes', 'resumen'));
    }
}
