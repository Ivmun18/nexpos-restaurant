<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoPago;
use Barryvdh\DomPDF\Facade\Pdf;

class ReciboController extends Controller
{
    public function recibo(ActoNotarial $acto, ActoPago $pago)
    {
        $acto->load(['cliente', 'usuario', 'pagos']);
        $empresa = \App\Models\Empresa::find($acto->empresa_id);

        $tiposActo = [
            'escritura_publica' => 'Escritura Pública',
            'poder'             => 'Poder Notarial',
            'testamento'        => 'Testamento',
            'legalizacion'      => 'Legalización',
            'carta_notarial'    => 'Carta Notarial',
            'protesto'          => 'Protesto',
            'acta_notarial'     => 'Acta Notarial',
            'otro'              => 'Acto Notarial',
        ];

        $tiposPago = [
            'adelanto'    => 'ADELANTO',
            'pago_parcial'=> 'PAGO PARCIAL',
            'pago_final'  => 'PAGO FINAL',
        ];

        $data = [
            'empresa'     => $empresa,
            'acto'        => $acto,
            'pago'        => $pago,
            'tipo_acto'   => $tiposActo[$acto->tipo_acto] ?? 'Acto Notarial',
            'tipo_pago'   => $tiposPago[$pago->tipo] ?? 'PAGO',
            'saldo'       => round($acto->monto_cobrar - $acto->monto_pagado, 2),
        ];

        $pdf = Pdf::loadView('pdf.recibo-notaria', $data)
            ->setPaper([0, 0, 226.77, 400], 'portrait'); // 80mm ticket

        return $pdf->stream('recibo-' . $acto->numero_expediente . '.pdf');
    }

    public function reciboUltimo(ActoNotarial $acto)
    {
        $pago = $acto->pagos()->latest()->first();
        if (!$pago) {
            return back()->with('error', 'No hay pagos registrados.');
        }
        return $this->recibo($acto, $pago);
    }
}
