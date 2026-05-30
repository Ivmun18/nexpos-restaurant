<?php
namespace App\Http\Controllers\Notaria;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportesNotariaController extends Controller
{
    public function exportarActosPdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $empresa   = auth()->user()->empresa;
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $actos = DB::table('actos_notariales as a')
            ->leftJoin('clientes as c', 'a.cliente_id', '=', 'c.id')
            ->leftJoin('users as u', 'a.usuario_id', '=', 'u.id')
            ->leftJoin('comprobantes_sunat as cs', 'cs.acto_id', '=', 'a.id')
            ->where('a.empresa_id', $empresaId)
            ->whereBetween('a.fecha_ingreso', [$desde, $hasta])
            ->select(
                'a.id', 'a.numero_expediente', 'a.tipo_acto', 'a.asunto',
                'a.fecha_ingreso', 'a.fecha_entrega', 'a.estado', 'a.estado_pago',
                'a.monto_cobrar', 'a.monto_pagado',
                'c.razon_social as cliente_nombre',
                'c.numero_documento as cliente_documento',
                'u.name as usuario',
                'cs.serie', 'cs.numero as num_comprobante', 'cs.tipo_comprobante'
            )
            ->orderBy('a.fecha_ingreso')
            ->get()
            ->map(function($a) {
                $a->saldo = round($a->monto_cobrar - $a->monto_pagado, 2);
                $a->comprobante = $a->serie ? $a->serie . '-' . str_pad($a->num_comprobante, 8, '0', STR_PAD_LEFT) : '-';
                $a->fecha_ingreso = \Carbon\Carbon::parse($a->fecha_ingreso)->format('d/m/Y');
                $a->fecha_entrega = $a->fecha_entrega ? \Carbon\Carbon::parse($a->fecha_entrega)->format('d/m/Y') : '-';
                return $a;
            });

        $totalCobrar = $actos->sum('monto_cobrar');
        $totalPagado = $actos->sum('monto_pagado');
        $totalSaldo  = $actos->sum('saldo');

        $html = view('pdf.reporte-actos-notaria', compact(
            'actos', 'empresa', 'desde', 'hasta',
            'totalCobrar', 'totalPagado', 'totalSaldo'
        ))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        $filename = 'Reporte_Actos_Notaria_' . $desde . '_a_' . $hasta . '.pdf';
        return $pdf->download($filename);
    }
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $comprobantes = DB::table('comprobantes_sunat')
            ->where('empresa_id', $empresaId)
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->orderByDesc('fecha_emision')
            ->get();

        $actos = DB::table('actos_notariales as a')
            ->leftJoin('clientes as c', 'a.cliente_id', '=', 'c.id')
            ->leftJoin('users as u', 'a.usuario_id', '=', 'u.id')
            ->leftJoin('comprobantes_sunat as cs', 'cs.acto_id', '=', 'a.id')
            ->where('a.empresa_id', $empresaId)
            ->whereBetween('a.fecha_ingreso', [$desde, $hasta])
            ->select(
                'a.id', 'a.numero_expediente', 'a.tipo_acto', 'a.asunto',
                'a.fecha_ingreso', 'a.fecha_entrega', 'a.estado', 'a.estado_pago',
                'a.monto_cobrar', 'a.monto_pagado',
                'c.razon_social as cliente_nombre',
                'c.numero_documento as cliente_documento',
                'c.tipo_documento',
                'u.name as usuario',
                'cs.serie', 'cs.numero as num_comprobante',
                'cs.tipo_comprobante', 'cs.estado as estado_comprobante'
            )
            ->orderByDesc('a.fecha_ingreso')
            ->get()
            ->map(function($a) {
                $a->saldo = round($a->monto_cobrar - $a->monto_pagado, 2);
                $a->comprobante = $a->serie ? $a->serie . '-' . str_pad($a->num_comprobante, 8, '0', STR_PAD_LEFT) : null;
                return $a;
            });

        return Inertia::render('Notaria/Reportes/Index', compact('comprobantes', 'actos', 'desde', 'hasta'));
    }
}
