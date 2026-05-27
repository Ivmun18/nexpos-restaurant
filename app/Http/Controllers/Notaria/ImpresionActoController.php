<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use Barryvdh\DomPDF\Facade\Pdf;

class ImpresionActoController extends Controller
{
    /**
     * Generar PDF del acto notarial para impresión y firma
     */
    public function imprimir(ActoNotarial $acto)
    {
        // Cargar todas las relaciones necesarias
        $acto->load([
            'cliente',
            'usuario',
            'empresa',
            'partes' => function($query) {
                $query->orderBy('orden');
            },
            'datos',
            'documentos',
            'requisitos',
            'seguimientos' => function($query) {
                $query->orderBy('created_at');
            }
        ]);

        // Convertir datos a array asociativo
        $datosMapa = $acto->datos->pluck('valor', 'campo')->toArray();

        // Generar PDF
        $pdf = PDF::loadView('pdf.acto-notarial', [
            'acto' => $acto,
            'datos' => $datosMapa,
        ]);

        // Configurar PDF
        $pdf->setPaper('a4', 'portrait');
        
        $nombreArchivo = $acto->numero_expediente . '_' . str_replace(' ', '_', $acto->tipo_acto) . '.pdf';
        
        return $pdf->stream($nombreArchivo);
    }

    /**
     * Descargar PDF (en lugar de verlo en navegador)
     */
    public function descargar(ActoNotarial $acto)
    {
        $acto->load([
            'cliente',
            'usuario',
            'empresa',
            'partes' => function($query) {
                $query->orderBy('orden');
            },
            'datos',
            'documentos',
            'requisitos',
        ]);

        $datosMapa = $acto->datos->pluck('valor', 'campo')->toArray();

        $pdf = PDF::loadView('pdf.acto-notarial', [
            'acto' => $acto,
            'datos' => $datosMapa,
        ]);

        $pdf->setPaper('a4', 'portrait');
        
        $nombreArchivo = $acto->numero_expediente . '_' . str_replace(' ', '_', $acto->tipo_acto) . '.pdf';
        
        return $pdf->download($nombreArchivo);
    }
}
