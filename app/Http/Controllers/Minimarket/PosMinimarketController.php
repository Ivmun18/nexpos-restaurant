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
            ->with(['presentaciones' => fn($q) => $q->where('activo', true), 'categoria:id,nombre,icono,color'])
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'descripcion_corta', 'codigo_barras', 'precio_venta', 'stock_actual', 'categoria_id', 'unidad_medida']);

        $instituciones = \App\Models\InstitucionMinimarket::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'porcentaje_recargo']);

        return Inertia::render('Minimarket/Pos', [
            'productos'     => $productos,
            'caja_abierta'  => $cajaAbierta,
            'instituciones' => $instituciones,
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
        'institucion_id'    => 'nullable|exists:instituciones_minimarket,id',
        'recargo_monto'     => 'nullable|numeric',
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

        $exonerado_flag = (bool) ($empresa->zona_exonerada ?? false);
        $esRus = $exonerado_flag;

        $gravado = $exonerado_flag ? 0 : round($request->total / 1.18, 2);
        $igv = $exonerado_flag ? 0 : round($request->total - $gravado, 2);
        $exonerado = $exonerado_flag ? round($request->total, 2) : 0;
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
            'institucion_id'      => $request->institucion_id,
            'recargo_monto'       => $request->recargo_monto ?? 0,
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
                    if ($pres && $pres->producto_id === $producto->id) {
                        $factorDescuento = (float) $pres->factor_conversion;
                    }
                }
                $producto->decrement('stock_actual', $item['cantidad'] * $factorDescuento);
            }
        }

        return $venta;
    });

    if ($tipoComprobante !== 'ninguno' && $empresa->apisunat_token) {
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
    if (empty($empresa->apisunat_token) || empty($empresa->apisunat_ruc)) {
        \Log::warning('emitirApisunat minimarket: faltan credenciales apisunat para empresa ' . $empresa->id);
        $venta->update(['observaciones' => 'No se emitio: faltan credenciales ApiSunat']);
        return;
    }

    $exonerada     = $esRus;
    $total         = round($venta->total, 2);
    $igv           = round($venta->total_igv ?? 0, 2);
    $baseImponible = $exonerada
        ? round($venta->total_exonerado ?? $total, 2)
        : round($venta->total_gravado ?? ($total - $igv), 2);

    $tipoDoc  = $venta->tipo_comprobante === '01' ? '01' : '03';
    $fileName = $empresa->ruc . '-' . $tipoDoc . '-' . $venta->serie . '-' . str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT);

    $lineas = [];
    foreach ($items as $idx => $i) {
        $cantidad     = (float) $i['cantidad'];
        $valorUnit    = (float) $i['valor_unitario'];
        $valLinea     = round($valorUnit * $cantidad, 2);
        $igvLinea     = $exonerada ? 0 : round($valLinea * ((float)$i['porcentaje_igv'] / 100), 2);

        $lineas[] = [
            'cbc:ID'                  => ['_text' => (string)($idx + 1)],
            'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => $i['unidad_de_medida'] ?? 'NIU'], '_text' => (string) $cantidad],
            'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valLinea],
            'cac:PricingReference' => [
                'cac:AlternativeConditionPrice' => [
                    'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => round((float) $i['precio_unitario'], 2)],
                    'cbc:PriceTypeCode' => ['_text' => '01'],
                ],
            ],
            'cac:TaxTotal' => [
                'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvLinea],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valLinea],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvLinea],
                    'cac:TaxCategory'   => [
                        'cbc:Percent'                => ['_text' => (string)(float) $i['porcentaje_igv']],
                        'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                        'cac:TaxScheme' => [
                            'cbc:ID'          => ['_text' => $i['codigo_tipo_afectacion_igv'] === '40' ? '9998' : ($exonerada ? '9997' : '1000')],
                            'cbc:Name'        => ['_text' => $i['nombre_tributo']],
                            'cbc:TaxTypeCode' => ['_text' => 'VAT'],
                        ],
                    ],
                ]],
            ],
            'cac:Item' => [
                'cbc:Description' => ['_text' => $i['descripcion']],
                'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => $i['codigo'] ?? 'S/C']],
            ],
            'cac:Price' => [
                'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valorUnit],
            ],
        ];
    }

    $documentBody = [
        'cbc:UBLVersionID'         => ['_text' => '2.1'],
        'cbc:CustomizationID'      => ['_text' => '2.0'],
        'cbc:ID'                   => ['_text' => $venta->serie . '-' . str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT)],
        'cbc:IssueDate'            => ['_text' => \Carbon\Carbon::parse($venta->fecha_emision ?? now())->format('Y-m-d')],
        'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $tipoDoc],
        'cbc:Note'                 => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => $this->numeroALetrasMinimarket($total)],
        'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
        'cac:PaymentTerms'         => [
            'cbc:ID'             => ['_text' => 'FormaPago'],
            'cbc:PaymentMeansID' => ['_text' => 'Contado'],
        ],
        'cac:AccountingSupplierParty' => [
            'cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                'cac:PartyLegalEntity'    => [
                    'cbc:RegistrationName' => ['_text' => $empresa->razon_social],
                    'cac:RegistrationAddress' => [
                        'cbc:AddressTypeCode' => ['_text' => '0000'],
                        'cac:AddressLine'     => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']],
                    ],
                ],
            ],
        ],
        'cac:AccountingCustomerParty' => [
            'cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $venta->cliente_tipo_doc ?: '1'], '_text' => $venta->cliente_num_doc ?: '00000000']],
                'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($venta->cliente_razon_social ?: 'CLIENTES VARIOS')]],
            ],
        ],
        'cac:TaxTotal' => [
            'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
            'cac:TaxSubtotal' => [[
                'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                'cac:TaxCategory'   => ['cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']]],
            ]],
        ],
        'cac:LegalMonetaryTotal' => [
            'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
            'cbc:TaxInclusiveAmount'  => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
            'cbc:PayableAmount'       => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
        ],
        'cac:InvoiceLine' => $lineas,
    ];

    try {
        $response = \Illuminate\Support\Facades\Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(60)
            ->post('https://back.apisunat.com/personas/v1/sendBill', [
                'personaId'    => $empresa->apisunat_ruc,
                'personaToken' => $empresa->apisunat_token,
                'fileName'     => $fileName,
                'documentBody' => $documentBody,
            ]);

        $data = $response->json();
        \Log::info('ApiSunat minimarket response venta ' . $venta->id . ': ' . json_encode($data));

        $aceptada = $response->successful() && (
            isset($data['sunatResponse']) ||
            (isset($data['pdf']) && isset($data['documentId']))
        );

        $pdfUrl = $data['sunatResponse']['enlace_del_pdf']
            ?? $data['pdf']['80mm']
            ?? $data['pdf']['A4']
            ?? null;

        $venta->update([
            'nubefact_id'     => $pdfUrl,
            'nubefact_estado' => $aceptada ? 'aceptado' : 'rechazado',
            'estado'          => $aceptada ? 'emitido' : 'pendiente',
            'observaciones'   => json_encode($data),
        ]);
    } catch (\Exception $e) {
        \Log::error('ApiSunat minimarket error venta ' . $venta->id . ': ' . $e->getMessage());
        $venta->update([
            'nubefact_estado' => 'rechazado',
            'observaciones'   => 'Error al emitir: ' . $e->getMessage(),
        ]);
    }
}

private function numeroALetrasMinimarket($numero)
{
    $entero  = (int) floor($numero);
    $decimal = (int) round(($numero - $entero) * 100);
    return strtoupper($this->convertirNumeroALetrasMinimarket($entero)) . " CON {$decimal}/100 SOLES";
}

private function convertirNumeroALetrasMinimarket($num)
{
    if ($num == 0) return 'cero';
    $unidades   = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    $decenas    = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciseis', 'diecisiete', 'dieciocho', 'diecinueve'];
    $decenasMul = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    $centenas   = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    if ($num < 10) return $unidades[$num];
    if ($num < 20) return $decenas[$num - 10];
    if ($num < 100) {
        $d = intdiv($num, 10);
        $u = $num % 10;
        return $decenasMul[$d] . ($u > 0 ? ' y ' . $unidades[$u] : '');
    }
    if ($num < 1000) {
        $c = intdiv($num, 100);
        $r = $num % 100;
        $texto = $num == 100 ? 'cien' : $centenas[$c];
        return $texto . ($r > 0 ? ' ' . $this->convertirNumeroALetrasMinimarket($r) : '');
    }
    if ($num < 1000000) {
        $m = intdiv($num, 1000);
        $r = $num % 1000;
        $prefijo = $m == 1 ? 'mil' : $this->convertirNumeroALetrasMinimarket($m) . ' mil';
        return $prefijo . ($r > 0 ? ' ' . $this->convertirNumeroALetrasMinimarket($r) : '');
    }
    return (string) $num;
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