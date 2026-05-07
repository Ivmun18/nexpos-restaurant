<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosMinimarketController extends Controller
{
    public function index()
    {
        $productos = Producto::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'descripcion_corta', 'codigo_barras', 'precio_venta', 'stock_actual', 'categoria_id']);

        return Inertia::render('Minimarket/Pos', [
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'items'        => 'required|array|min:1',
        'metodo_pago'  => 'required|string',
        'total'        => 'required|numeric',
        'monto_pagado' => 'nullable|numeric',
    ]);

    $empresa = auth()->user()->empresa;
    $tipoComprobante = $request->tipo_comprobante ?? 'ninguno';

    // Definir serie y tipo según comprobante
    if ($tipoComprobante === 'factura') {
        $tipo = '01';
        $serie = $empresa->serie_factura ?? 'F001';
    } else {
        $tipo = '03';
        $serie = $empresa->serie_boleta ?? 'B001';
    }

    $correlativo = (\App\Models\Venta::where('empresa_id', $empresa->id)
        ->where('serie', $serie)->max('correlativo') ?? 0) + 1;

    // Zona exonerada no tiene IGV
    if ($empresa->zona_exonerada) {
        $igv = 0;
        $gravado = 0;
        $exonerado = $request->total;
    } else {
        $igv = round($request->total / 1.18 * 0.18, 2);
        $gravado = round($request->total - $igv, 2);
        $exonerado = 0;
    }

    $venta = \App\Models\Venta::create([
        'empresa_id'          => $empresa->id,
        'usuario_id'          => auth()->id(),
        'tipo_comprobante'    => $tipo,
        'serie'               => $serie,
        'correlativo'         => $correlativo,
        'numero_completo'     => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT),
        'fecha_emision'       => now()->toDateString(),
        'hora_emision'        => now()->toTimeString(),
        'moneda'              => 'PEN',
        'total_gravado'       => $gravado,
        'total_exonerado'     => $exonerado ?? 0,
        'total_inafecto'      => 0,
        'total_igv'           => $igv,
        'total_descuento'     => 0,
        'metodo_pago'         => $request->metodo_pago,
        'cliente_tipo_doc'    => $tipoComprobante === 'factura' ? '6' : '1',
        'cliente_num_doc'     => $request->cliente_dni ?? '',
        'cliente_razon_social'=> $request->cliente_razon_social ?? '',
        'cliente_email'       => $request->cliente_email ?? '',
    ]);

   foreach ($request->items as $index => $item) {
    \App\Models\VentaDetalle::create([
        'venta_id'           => $venta->id,
        'producto_id'        => $item['id'],
        'linea'              => $index + 1,
        'codigo_producto'    => $item['codigo_barras'] ?? $item['codigo'] ?? 'S/C',
        'descripcion'        => $item['descripcion'],
        'unidad_medida'      => 'NIU',
        'cantidad'           => $item['cantidad'],
        'precio_unitario'    => $item['precio_venta'],
        'valor_unitario'     => $item['precio_venta'],
        'descuento_monto'    => 0,
        'tipo_afectacion_igv'=> '10',
        'total_valor_venta'  => $item['cantidad'] * $item['precio_venta'],
        'total_igv'          => 0,
        'total'              => $item['cantidad'] * $item['precio_venta'],
    ]);

    \App\Models\Producto::where('id', $item['id'])->decrement('stock_actual', $item['cantidad']);
}

    // Emitir comprobante en Nubefact si corresponde
    if ($tipoComprobante !== 'ninguno' && $empresa->nubefact_token) {
        $this->emitirNubefact($venta, $empresa);
    }

    return redirect()->route('minimarket.ventas.show', $venta->id)
        ->with('success', 'Venta registrada correctamente')
        ->with('imprimir', true);
}

private function emitirNubefact($venta, $empresa)
{
    $venta->load('detalle');

    $items = $venta->detalle->map(function($item, $i) {
        $valorUnitario = round($item->precio_unitario / 1.18, 4);
        $igvItem = round($valorUnitario * 0.18 * $item->cantidad, 2);
        return [
            'unidad_de_medida'         => 'NIU',
            'codigo'                   => $item->codigo_producto ?? 'S/C',
            'descripcion'              => $item->descripcion,
            'cantidad'                 => $item->cantidad,
            'valor_unitario'           => $valorUnitario,
            'precio_unitario'          => $item->precio_unitario,
            'descuento'                => '0',
            'subtotal'                 => round($valorUnitario * $item->cantidad, 2),
            'tipo_de_igv'              => 1,
            'igv'                      => $igvItem,
            'total'                    => round($item->precio_unitario * $item->cantidad, 2),
            'anticipo_regularizacion'  => false,
            'anticipo_documento_serie' => '',
            'anticipo_documento_numero'=> '',
        ];
    })->toArray();

    $url = $empresa->nubefact_demo
        ? 'https://demo-api.nubefact.com/api/v1/'
        : 'https://api.nubefact.com/api/v1/';

    $payload = [
        'operacion'           => 'generar_comprobante',
        'tipo_de_comprobante' => $venta->tipo_comprobante === '01' ? 1 : 2,
        'serie'               => $venta->serie,
        'numero'              => $venta->correlativo,
        'sunat_transaction'   => 1,
        'cliente_tipo_de_documento' => $venta->tipo_comprobante === '01' ? 6 : 1,
        'cliente_numero_de_documento' => $venta->cliente_num_doc ?? '',
        'cliente_denominacion'=> $venta->cliente_razon_social ?? 'CLIENTE',
        'cliente_direccion'   => '',
        'cliente_email'       => $venta->cliente_email ?? '',
        'fecha_de_emision'    => now()->format('d-m-Y'),
        'hora_de_emision'     => now()->format('H:i:s'),
        'moneda'              => 1,
        'tipo_de_cambio'      => '',
        'porcentaje_de_igv'   => 18.0,
        'total_gravada'       => $venta->total_gravado,
        'total_igv'           => $venta->total_igv,
        'total'               => $venta->total_gravado + $venta->total_igv,
        'detalle'             => $items,
        'enviar_automaticamente_a_la_sunat' => true,
        'enviar_automaticamente_al_cliente' => !empty($venta->cliente_email),
        'codigo_unico'        => $venta->id,
        'condiciones_de_pago' => 'Contado',
    ];

    try {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Token token=' . $empresa->nubefact_token,
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            $venta->update([
                'nubefact_id'     => $data['enlace_del_pdf'] ?? null,
                'nubefact_estado' => 'aceptado',
                'observaciones'   => json_encode($data),
            ]);
        } else {
            $error = $response->json();
            $venta->update([
                'nubefact_estado' => 'rechazado',
                'observaciones'   => json_encode($error),
            ]);
        }
    } catch (\Exception $e) {
        \Log::error('Nubefact error: ' . $e->getMessage());
        $venta->update([
            'nubefact_estado' => 'rechazado',
            'observaciones'   => $e->getMessage(),
        ]);
    }
}
}