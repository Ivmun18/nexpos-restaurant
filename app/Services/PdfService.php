<?php

namespace App\Services;

use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generarTicket(Venta $venta): mixed
    {
        $venta->load('detalle');
        $pdf = Pdf::loadView('pdf.ticket', compact('venta'));
        $pdf->setPaper([0, 0, 226.77, 800], 'portrait'); // 80mm ancho
        return $pdf;
    }

    public function generarA4(Venta $venta): mixed
    {
        $venta->load('detalle');
        $pdf = Pdf::loadView('pdf.comprobante_a4', compact('venta'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf;
    }
}