<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use App\Models\AuditoriaLog;
use Inertia\Inertia;

class PosFarmaciaController extends Controller
{
    public function index()
    {
        $productos = Producto::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'descripcion_corta', 'codigo_barras', 'precio_venta', 'stock_actual', 'stock_minimo', 'categoria_id', 'fecha_vencimiento', 'lote', 'laboratorio', 'principio_activo', 'presentacion', 'concentracion', 'requiere_receta']);

        // Calcular estado de vencimiento de cada producto
        $productos = $productos->map(function($p) {
            if ($p->fecha_vencimiento) {
                $dias = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($p->fecha_vencimiento)->startOfDay(), false);
                $p->dias_para_vencer = $dias;
                if ($dias < 0) {
                    $p->estado_vencimiento = 'vencido';
                } elseif ($dias <= 30) {
                    $p->estado_vencimiento = 'critico';
                } elseif ($dias <= 90) {
                    $p->estado_vencimiento = 'proximo';
                } else {
                    $p->estado_vencimiento = 'ok';
                }
            } else {
                $p->dias_para_vencer = null;
                $p->estado_vencimiento = 'sin_fecha';
            }
            return $p;
        });

        // Verificar caja abierta
        $empresaId = auth()->user()->empresa_id;
        $cajaAbierta = \App\Models\CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')
            ->latest()->first();

        if (!$cajaAbierta) {
            return redirect()->route('farmacia.caja')->with('warning', '⚠️ Debes abrir la caja antes de vender.');
        }

        $clientes = \App\Models\Cliente::where('empresa_id', $empresaId)
            ->orderBy('razon_social')
            ->get(['id', 'razon_social', 'numero_documento', 'tipo_documento', 'email', 'telefono']);

        return Inertia::render('Farmacia/Pos', [
            'productos'    => $productos,
            'caja_abierta' => $cajaAbierta,
            'clientes'     => $clientes,
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

    // Calcular IGV según régimen tributario
    $regimen = $empresa->regimen_tributario ?? 'GENERAL';
    $esRus = in_array($regimen, ['RUS', 'EXONERADO']) || $empresa->zona_exonerada;
    if ($regimen === 'RUS') {
        $igv      = 0;
        $gravado  = 0;
        $exonerado= 0;
        $inafecto = $request->total;
    } elseif ($regimen === 'EXONERADO' || $empresa->zona_exonerada) {
        $igv      = 0;
        $gravado  = 0;
        $exonerado= $request->total;
        $inafecto = 0;
    } else {
        // GENERAL, MYPE, RER — todos con IGV 18%
        $igv      = round($request->total / 1.18 * 0.18, 2);
        $gravado  = round($request->total - $igv, 2);
        $exonerado= 0;
        $inafecto = 0;
    }

    $modalidad = $empresa->modalidad_cobro ?? 'directo';
    $estadoVenta = $modalidad === 'cajero' ? 'pendiente' : 'emitido';

    // Buscar caja abierta para vincular la venta
    $cajaAbierta = \App\Models\CajaMinimarket::where('empresa_id', $empresa->id)
        ->where('estado', 'abierta')
        ->latest()->first();

    if (!$cajaAbierta) {
        return back()->withErrors(['caja' => '⚠️ Debes abrir la caja antes de vender.']);
    }

    // Validar que ningún producto esté vencido
    $idsItems = collect($request->items)->pluck('id')->toArray();
    $productosVencidos = \App\Models\Producto::whereIn('id', $idsItems)
        ->whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<', now()->toDateString())
        ->pluck('descripcion');
    if ($productosVencidos->isNotEmpty()) {
        return back()->withErrors(['vencidos' => '❌ No se puede vender productos vencidos: ' . $productosVencidos->implode(', ')]);
    }

    // Productos que requieren receta — solo registro, no se bloquea la venta
    // (Modo libre: el cajero/dueño decide si exige receta o no)
    // Los datos de receta se guardarán si fueron provistos. Si no, queda registrado como venta sin receta.

    $venta = \App\Models\Venta::create([
        'empresa_id'          => $empresa->id,
        'usuario_id'          => auth()->id(),
        'caja_id'             => $cajaAbierta->id,
        'receta_medico_nombre'=> $request->receta_medico_nombre ?? null,
        'receta_medico_cmp'   => $request->receta_medico_cmp ?? null,
        'receta_numero'       => $request->receta_numero ?? null,
        'receta_fecha'        => $request->receta_fecha ?? null,
        'receta_observaciones'=> $request->receta_observaciones ?? null,
        'estado'              => $estadoVenta,
        'tipo_comprobante'    => $tipo,
        'serie'               => $serie,
        'correlativo'         => $correlativo,
        'numero_completo'     => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT),
        'fecha_emision'       => now()->toDateString(),
        'hora_emision'        => now()->toTimeString(),
        'moneda'              => 'PEN',
        'total_gravado'       => $gravado,
        'total_exonerado'     => $exonerado ?? 0,
        'total_inafecto'      => $inafecto ?? 0,
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
    // Obtener lote y fecha de vencimiento actuales del producto (snapshot)
    $productoSnapshot = \App\Models\Producto::find($item['id']);

    \App\Models\VentaDetalle::create([
        'venta_id'           => $venta->id,
        'producto_id'        => $item['id'],
        'linea'              => $index + 1,
        'codigo_producto'    => $item['codigo_barras'] ?? $item['codigo'] ?? 'S/C',
        'descripcion'        => $item['descripcion'],
        'lote'               => $productoSnapshot->lote ?? null,
        'fecha_vencimiento'  => $productoSnapshot->fecha_vencimiento ?? null,
        'unidad_medida'      => 'NIU',
        'cantidad'           => $item['cantidad'],
        'precio_unitario'    => $item['precio_venta'],
        'valor_unitario'     => $item['precio_venta'],
        'descuento_monto'    => 0,
        'tipo_afectacion_igv'=> ($esRus || $empresa->zona_exonerada) ? '30' : '10',
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

    // ============ AUDITORÍA ============
    $descripcionVenta = ($venta->tipo_comprobante ?? 'comprobante') . ' ' . $venta->numero_completo . ' · S/ ' . number_format($venta->total, 2);
    $severidad = 'info';
    $accion = 'creada';
    
    // Detectar si vendió producto vencido o sin receta
    $alertasVenta = [];
    foreach ($request->items as $item) {
        $prod = \App\Models\Producto::find($item['id']);
        if ($prod && $prod->fecha_vencimiento) {
            $diasVence = now()->diffInDays($prod->fecha_vencimiento, false);
            if ($diasVence < 0) $alertasVenta[] = "Vendió producto VENCIDO: {$prod->descripcion}";
            elseif ($diasVence < 30) $alertasVenta[] = "Vendió producto próximo a vencer ({$diasVence}d): {$prod->descripcion}";
        }
        if ($prod && $prod->requiere_receta && ($request->receta_medico_nombre ?? '') === 'SIN RECETA') {
            $alertasVenta[] = "Vendió SIN RECETA: {$prod->descripcion}";
        }
    }
    
    if (count($alertasVenta) > 0) {
        $severidad = 'warning';
        $accion = 'creada_con_alertas';
        $descripcionVenta .= ' · ⚠️ ' . implode(' | ', $alertasVenta);
    }
    
    \App\Models\AuditoriaLog::registrar(
        'venta',
        $accion,
        'venta',
        $venta->id,
        $descripcionVenta,
        null,
        ['total' => $venta->total, 'items' => count($request->items), 'metodo_pago' => $request->metodo_pago],
        'Venta registrada por ' . auth()->user()->name,
        $severidad
    );
    
    return redirect()->route('farmacia.ventas.show', $venta->id)
        ->with('success', 'Venta registrada correctamente')
        ->with('imprimir', true);
}

private function emitirNubefact($venta, $empresa)
{
    $venta->load('detalle');
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