<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoPago;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            'acta'              => 'Acta Notarial',
            'protesto'          => 'Protesto',
            'compraventa'       => 'Compraventa',
            'minuta'            => 'Minuta',
            'otro'              => 'Acto Notarial',
        ];

        $tiposPago = [
            'adelanto'    => 'ADELANTO',
            'pago_parcial'=> 'PAGO PARCIAL',
            'pago_final'  => 'PAGO FINAL',
        ];

        // Construir URL del portal con parámetros pre-llenados
        $numeroDocumento = $acto->cliente->numero_documento ?? '';
        $numeroExpediente = $acto->numero_expediente;
        
        // URL base
        $baseUrl = config('app.url');
        $portalUrl = $baseUrl . '/portal-cliente?doc=' . urlencode($numeroDocumento) . '&exp=' . urlencode($numeroExpediente);

        // Generar QR en formato PNG y convertir a base64
        $qrCode = QrCode::format('png')->size(300)->margin(1)->generate($portalUrl);
        $qrCodeBase64 = base64_encode($qrCode);

        $data = [
            'empresa'        => $empresa,
            'acto'           => $acto,
            'pago'           => $pago,
            'tiposActo'      => $tiposActo,
            'tipo_pago'      => $tiposPago[$pago->tipo] ?? 'PAGO',
            'saldo'          => round($acto->monto_cobrar - $acto->monto_pagado, 2),
            'portalUrl'      => $portalUrl,
            'qrCodeBase64'   => $qrCodeBase64,
        ];

        $pdf = Pdf::loadView('pdf.recibo-notaria', $data)
            ->setPaper([0, 0, 226.77, 600], 'portrait');

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
