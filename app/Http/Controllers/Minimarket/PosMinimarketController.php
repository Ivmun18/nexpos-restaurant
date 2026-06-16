<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosMinimarketController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        // Verificar caja abierta
        $cajaAbierta = \App\Models\CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')
            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('minimarket.caja')->with('warning', '⚠️ Debes abrir la caja antes de vender.');
        }

        $productos = Producto::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->with(['presentaciones' => fn($q) => $q->where('activo', true)])
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'descripcion_corta', 'codigo_barras', 'precio_venta', 'stock_actual', 'categoria_id', 'unidad_medida']);

        return Inertia::render('Minimarket/Pos', [
            'productos'    => $productos,
            'caja_abierta' => $cajaAbierta,
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'items'             => 'required|array|min:1',
        'items.*.id'        => 'required|integer',
        'items.*.cantidad'  => 'required|numeric|min:0.01',
        'metodo_pago'       => 'required|string',
        'total'             => 'required|numeric',
        'monto_pagado'      => 'nullable|numeric',
    ]);

    $empresa = auth()->user()->empresa;
    $tipoComprobante = $request->tipo_comprobante ?? 'ninguno';

    $venta = DB::transaction(function () use ($request, $empresa, $tipoComprobante) {
        if ($tipoComprobante === 'factura') {
            $tipo = '01';
            $serie = $empresa->serie_factura ?? 'F001';
        } else {
            $tipo = '03';
            $serie = $empresa->serie_boleta ?? 'B001';
        }

        $ultimaVenta = \App\Models\Venta::where('empresa_id', $empresa->id)
            ->where('serie', $serie)
            ->lockForUpdate()
            ->max('correlativo');
        $correlativo = ($ultimaVenta ?? 0) + 1;

        $productosIds = collect($request->items)->pluck('id')->all();
        $productos = \App\Models\Producto::where('empresa_id', $empresa->id)
            ->whereIn('id', $productosIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($request->items as $item) {
            $producto = $productos->get($item['id']);
            if (!$producto) {
                throw new \Exception("Producto no encontrado o no pertenece a esta empresa (id: {$item['id']})");
            }
            $factor = 1;
            if (!empty($item['presentacion_id'])) {
                $presentacion = \App\Models\ProductoPresentacion::where('id', $item['presentacion_id'])
                    ->where('producto_id', $producto->id)
                    ->first();
                if (!$presentacion) {
                    throw new \Exception("Presentacion invalida para '{$producto->descripcion}'");
                }
                $factor = (float) $presentacion->factor_conversion;
            }
            $cantidadEnStock = $item['cantidad'] * $factor;

            if ($producto->controla_stock && $producto->stock_actual < $cantidadEnStock) {
                throw new \Exception("Stock insuficiente para '{$producto->descripcion}'. Disponible: {$producto->stock_actual}, solicitado: {$cantidadEnStock}");
            }
        }

        $esRus = $empresa->regimen_tributario === 'RUS';
        $zonaExonerada = (bool) ($empresa->zona_exonerada ?? false);

        if ($esRus || $zonaExonerada) {
            $gravado = 0;
            $exonerado = round($request->total, 2);
            $igv = 0;
        } else {
            $gravado = round($request->total / 1.18, 2);
            $exonerado = 0;
            $igv = round($request->total - $gravado, 2);
        }
        $inafecto = 0;

        $venta = \App\Models\Venta::create([
            'empresa_id'          => $empresa->id,
            'usuario_id'          => auth()->id(),
            'estado'              => 'pendiente',
            'tipo_comprobante'    => $tipo,
            'serie'               => $serie,
            'correlativo'         => $correlativo,
            'numero_completo'     => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT),
            'fecha_emision'       => now()->toDateString(),
            'hora_emision'        => now()->toTimeString(),
            'moneda'              => 'PEN',
            'total_gravado'       => $gravado,
            'total_exonerado'     => $exonerado,
            'total_inafecto'      => $inafecto,
            'total_igv'           => $igv,
            'total'               => $request->total,
            'total_descuento'     => 0,
            'metodo_pago'         => $request->metodo_pago,
            'cliente_tipo_doc'    => $tipoComprobante === 'factura' ? '6' : '1',
            'cliente_num_doc'     => $request->cliente_dni ?? '',
            'cliente_razon_social'=> $request->cliente_razon_social ?? '',
            'cliente_email'       => $request->cliente_email ?? '',
        ]);

        foreach ($request->items as $index => $item) {
            $producto = $productos->get($item['id']);

            \App\Models\VentaDetalle::create([
                'venta_id'           => $venta->id,
                'producto_id'        => $item['id'],
                'linea'              => $index + 1,
                'codigo_producto'    => $item['codigo_barras'] ?? $item['codigo'] ?? 'S/C',
                'descripcion'        => $item['descripcion'],
                'unidad_medida'      => $item['unidad_sunat'] ?? 'NIU',
                'cantidad'           => $item['cantidad'],
                'precio_unitario'    => $item['precio_venta'],
                'valor_unitario'     => $item['precio_venta'],
                'descuento_monto'    => 0,
                'tipo_afectacion_igv'=> ($esRus || $empresa->zona_exonerada) ? '30' : '10',
                'total_valor_venta'  => $item['cantidad'] * $item['precio_venta'],
                'total_igv'          => 0,
                'total'              => $item['cantidad'] * $item['precio_venta'],
            ]);

            if ($producto->controla_stock) {
                $factorDescuento = 1;
                if (!empty($item['presentacion_id'])) {
                    $pres = \App\Models\ProductoPresentacion::find($item['presentacion_id']);
                    if ($pres) {
                        $factorDescuento = (float) $pres->factor_conversion;
                    }
                }
                $producto->decrement('stock_actual', $item['cantidad'] * $factorDescuento);
            }
        }

        return $venta;
    });

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
    $esRus     = $empresa->regimen_tributario === 'RUS' || $empresa->zona_exonerada;
    $proveedor = $empresa->proveedor_facturacion ?? 'apisunat';

    // Formatear items según régimen
    $items = $venta->detalle->map(function($item) use ($esRus) {
        if ($esRus) {
            return [
                'unidad_de_medida' => 'NIU',
                'codigo'           => $item->codigo_producto ?? 'S/C',
                'descripcion'      => $item->descripcion,
                'cantidad'         => $item->cantidad,
                'valor_unitario'   => $item->precio_unitario,
                'precio_unitario'  => $item->precio_unitario,
                'subtotal'         => round($item->precio_unitario * $item->cantidad, 2),
                'igv'              => 0,
                'total'            => round($item->precio_unitario * $item->cantidad, 2),
                // Nubefact
                'tipo_de_igv'              => 8,
                'descuento'                => '0',
                'anticipo_regularizacion'  => false,
                'anticipo_documento_serie' => '',
                'anticipo_documento_numero'=> '',
                // APISUNAT
                'porcentaje_igv'              => 0,
                'codigo_tipo_afectacion_igv'  => '40',
                'nombre_tributo'              => 'INA',
            ];
        }
        $valorUnitario = round($item->precio_unitario / 1.18, 4);
        $igvItem = round($valorUnitario * 0.18 * $item->cantidad, 2);
        return [
            'unidad_de_medida' => 'NIU',
            'codigo'           => $item->codigo_producto ?? 'S/C',
            'descripcion'      => $item->descripcion,
            'cantidad'         => $item->cantidad,
            'valor_unitario'   => $valorUnitario,
            'precio_unitario'  => $item->precio_unitario,
            'subtotal'         => round($valorUnitario * $item->cantidad, 2),
            'igv'              => $igvItem,
            'total'            => round($item->precio_unitario * $item->cantidad, 2),
            // Nubefact
            'tipo_de_igv'              => 1,
            'descuento'                => '0',
            'anticipo_regularizacion'  => false,
            'anticipo_documento_serie' => '',
            'anticipo_documento_numero'=> '',
            // APISUNAT
            'porcentaje_igv'             => 18,
            'codigo_tipo_afectacion_igv' => '10',
            'nombre_tributo'             => 'IGV',
        ];
    })->toArray();

    try {
        if ($proveedor === 'apisunat') {
            $this->emitirApisunat($venta, $empresa, $items, $esRus);
        } else {
            $this->emitirNubefactApi($venta, $empresa, $items, $esRus);
        }
    } catch (\Exception $e) {
        \Log::error('Error emision: ' . $e->getMessage());
        $venta->update([
            'nubefact_estado' => 'error',
            'observaciones'   => $e->getMessage(),
        ]);
    }
}

private function emitirApisunat($venta, $empresa, $items, $esRus)
{
    $payload = [
        'documento'                  => $venta->tipo_comprobante === '01' ? 'factura' : 'boleta',
        'serie'                      => $venta->serie,
        'numero'                     => $venta->correlativo,
        'fecha_de_emision'           => now()->format('Y-m-d'),
        'moneda'                     => 'PEN',
        'tipo_operacion'             => '0101',
        'cliente_tipo_de_documento'  => $venta->tipo_comprobante === '01' ? '6' : '1',
        'cliente_numero_de_documento'=> $venta->cliente_num_doc ?? '',
        'cliente_denominacion'       => $venta->cliente_razon_social ?? 'CLIENTES VARIOS',
        'cliente_direccion'          => '',
        'cliente_correo'             => $venta->cliente_email ?? '',
        'total_gravada'              => $venta->total_gravado ?? 0,
        'total_exonerada'            => $venta->total_exonerado ?? 0,
        'total_inafecta'             => $venta->total_inafecto ?? 0,
        'total_igv'                  => $venta->total_igv ?? 0,
        'total'                      => $venta->total,
        'items'                      => collect($items)->map(fn($i) => [
            'unidad_de_medida'           => $i['unidad_de_medida'],
            'descripcion'                => $i['descripcion'],
            'cantidad'                   => $i['cantidad'],
            'valor_unitario'             => $i['valor_unitario'],
            'porcentaje_igv'             => $i['porcentaje_igv'],
            'codigo_tipo_afectacion_igv' => $i['codigo_tipo_afectacion_igv'],
            'nombre_tributo'             => $i['nombre_tributo'],
        ])->toArray(),
    ];

    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Bearer ' . $empresa->nubefact_token,
        'Content-Type'  => 'application/json',
    ])->post('https://api.apisunat.com/v1/personas/' . $empresa->ruc . '/documentos', $payload);

    if ($response->successful()) {
        $data = $response->json();
        $venta->update([
            'nubefact_id'     => $data['payload']['pdf'] ?? null,
            'nubefact_estado' => 'aceptado',
            'observaciones'   => json_encode($data),
        ]);
    } else {
        $venta->update([
            'nubefact_estado' => 'rechazado',
            'observaciones'   => json_encode($response->json()),
        ]);
    }
}

private function emitirNubefactApi($venta, $empresa, $items, $esRus)
{
    $url = $empresa->nubefact_demo
        ? 'https://demo-api.nubefact.com/api/v1/'
        : 'https://api.nubefact.com/api/v1/';

    $payload = [
        'operacion'           => 'generar_comprobante',
        'tipo_de_comprobante' => $venta->tipo_comprobante === '01' ? 1 : 2,
        'serie'               => $venta->serie,
        'numero'              => $venta->correlativo,
        'sunat_transaction'   => 1,
        'cliente_tipo_de_documento'   => $venta->tipo_comprobante === '01' ? 6 : 1,
        'cliente_numero_de_documento' => $venta->cliente_num_doc ?? '',
        'cliente_denominacion'=> $venta->cliente_razon_social ?? 'CLIENTE',
        'cliente_direccion'   => '',
        'cliente_email'       => $venta->cliente_email ?? '',
        'fecha_de_emision'    => now()->format('d-m-Y'),
        'hora_de_emision'     => now()->format('H:i:s'),
        'moneda'              => 1,
        'tipo_de_cambio'      => '',
        'porcentaje_de_igv'   => $esRus ? 0.0 : 18.0,
        'total_gravada'       => $venta->total_gravado ?? 0,
        'total_exonerada'     => $venta->total_exonerado ?? 0,
        'total_inafecta'      => $venta->total_inafecto ?? 0,
        'total_igv'           => $venta->total_igv ?? 0,
        'total'               => $venta->total,
        'detalle'             => $items,
        'enviar_automaticamente_a_la_sunat' => true,
        'enviar_automaticamente_al_cliente' => !empty($venta->cliente_email),
        'codigo_unico'        => $venta->id,
        'condiciones_de_pago' => 'Contado',
    ];

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
        $venta->update([
            'nubefact_estado' => 'rechazado',
            'observaciones'   => json_encode($response->json()),
        ]);
    }
}
}