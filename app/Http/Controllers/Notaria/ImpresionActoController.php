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

        // Mapa tipo_acto -> plantilla blade notarial
        $mapaPlantillas = [
            'compra_venta'             => 'notaria.minuta-compraventa',
            'escritura_publica'        => 'notaria.minuta-compraventa',
            'compra_venta_bien_futuro' => 'notaria.minuta-compraventa',
            'compra_venta_hipoteca'    => 'notaria.minuta-compraventa',
            'compra_venta_alicuotas'   => 'notaria.minuta-compraventa',
        ];
        $blade = $mapaPlantillas[$acto->tipo_acto] ?? 'pdf.acto-notarial';

        // Aliases para compatibilidad entre campos del formulario y la plantilla
        if (!isset($datosMapa['lote_descripcion']) && isset($datosMapa['predio_descripcion'])) {
            $datosMapa['lote_descripcion'] = $datosMapa['predio_descripcion'];
        }

        $empresa = $acto->empresa;
        $vendedor = [
            'vendedor_tipo'              => $empresa->minuta_vendedor_tipo ?? 'empresa',
            'vendedor_razon_social'      => $empresa->minuta_vendedor_razon_social ?? '',
            'vendedor_ruc'               => $empresa->minuta_vendedor_ruc ?? '',
            'vendedor_domicilio'         => $empresa->minuta_vendedor_domicilio ?? '',
            'vendedor_partida_registral' => $empresa->minuta_vendedor_partida ?? '',
            'representante_cargo'        => $empresa->minuta_representante_cargo ?? 'Gerente General',
            'representante_nombre'       => $empresa->minuta_representante_nombre ?? '',
            'representante_dni'          => $empresa->minuta_representante_dni ?? '',
            'representante_estado_civil' => $empresa->minuta_representante_estado_civil ?? 'soltero',
            'representante_profesion'    => $empresa->minuta_representante_profesion ?? '',
            'representante_domicilio'    => $empresa->minuta_representante_domicilio ?? '',
            'ciudad'                     => $empresa->minuta_ciudad ?? 'Huánuco',
        ];

        $pdf = PDF::loadView($blade, [
            'acto'    => $acto,
            'datos'   => $datosMapa,
            'd'       => array_merge($vendedor, $datosMapa),
            'vendedor'=> $vendedor,
        ]);

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


        // Mapa tipo_acto -> plantilla blade notarial
        $mapaPlantillas = [
            'compra_venta'              => 'notaria.minuta-compraventa',
            'escritura_publica'         => 'notaria.minuta-compraventa',
            'compra_venta_bien_futuro'  => 'notaria.minuta-compraventa',
            'compra_venta_hipoteca'     => 'notaria.minuta-compraventa',
            'compra_venta_alicuotas'    => 'notaria.minuta-compraventa',
        ];
        $blade = $mapaPlantillas[$acto->tipo_acto] ?? 'pdf.acto-notarial';

        // Aliases para compatibilidad entre campos del formulario y la plantilla
        if (!isset($datosMapa['lote_descripcion']) && isset($datosMapa['predio_descripcion'])) {
            $datosMapa['lote_descripcion'] = $datosMapa['predio_descripcion'];
        }

        $empresa = $acto->empresa;
        $vendedor = [
            'vendedor_tipo'              => $empresa->minuta_vendedor_tipo ?? 'empresa',
            'vendedor_razon_social'      => $empresa->minuta_vendedor_razon_social ?? '',
            'vendedor_ruc'               => $empresa->minuta_vendedor_ruc ?? '',
            'vendedor_domicilio'         => $empresa->minuta_vendedor_domicilio ?? '',
            'vendedor_partida_registral' => $empresa->minuta_vendedor_partida ?? '',
            'representante_cargo'        => $empresa->minuta_representante_cargo ?? 'Gerente General',
            'representante_nombre'       => $empresa->minuta_representante_nombre ?? '',
            'representante_dni'          => $empresa->minuta_representante_dni ?? '',
            'representante_estado_civil' => $empresa->minuta_representante_estado_civil ?? 'soltero',
            'representante_profesion'    => $empresa->minuta_representante_profesion ?? '',
            'representante_domicilio'    => $empresa->minuta_representante_domicilio ?? '',
            'ciudad'                     => $empresa->minuta_ciudad ?? 'Huánuco',
        ];

        $pdf = PDF::loadView($blade, [
            'acto'    => $acto,
            'datos'   => $datosMapa,
            'd'       => array_merge($vendedor, $datosMapa),
            'vendedor'=> $vendedor,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $nombreArchivo = $acto->numero_expediente . '_' . str_replace(' ', '_', $acto->tipo_acto) . '.pdf';
        return $pdf->download($nombreArchivo);
    }
}
