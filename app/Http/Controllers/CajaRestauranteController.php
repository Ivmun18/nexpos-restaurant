<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\CajaRestaurante;
use App\Models\CajaMovimiento;
use App\Models\SesionCaja;
use App\Models\Receta;
use App\Models\InsumoMovimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CajaRestauranteController extends Controller
{
    // Devuelve los IDs de todas las mesas del grupo (principal + unidas)
    private function idsGrupoMesa(Mesa $mesa): array
    {
        // Si esta mesa es secundaria, la principal es su mesa_principal_id
        $principalId = $mesa->mesa_principal_id ?: $mesa->id;
        // El grupo = la principal + todas las que apuntan a ella
        $ids = Mesa::where('id', $principalId)
            ->orWhere('mesa_principal_id', $principalId)
            ->pluck('id')
            ->toArray();
        return $ids;
    }

    // Vista de cobro de una mesa
    public function show(Mesa $mesa): Response
    {
        $mesaIds = $this->idsGrupoMesa($mesa);

        $pedidos = Pedido::with('detalles')
            ->whereIn('mesa_id', $mesaIds)
            ->whereIn('estado', ['enviado', 'listo'])
            ->orderBy('numero_ronda')
            ->get();

        // Total bruto de la mesa (todos los platos)
        $total = $pedidos->sum('total');

        // Separar platos pagados de pendientes (para modo por platos)
        $todosDetalles   = $pedidos->flatMap->detalles->where('anulado', false);
        $totalPagadoPlatos = (float) $todosDetalles->where('pagado', true)->sum('subtotal');
        $totalPendiente    = (float) $todosDetalles->where('pagado', false)->sum('subtotal');
        $platosPendientes  = $todosDetalles->where('pagado', false)->count();
        $platosPagados     = $todosDetalles->where('pagado', true)->count();

        // Acumulado de cobros por PARTES iguales (no cuenta los cobros por platos)
        $pagadoAcumulado = (float) CajaRestaurante::where('mesa_id', $mesa->id)
            ->where('partes_total', '>', 1)
            ->where('cuenta_total', $total)
            ->whereHas('mesa', function ($q) {
                $q->where('estado', 'ocupada');
            })
            ->sum('monto_pagado');

        return Inertia::render('CajaRestaurante/Show', [
            'mesa'              => $mesa,
            'pedidos'           => $pedidos,
            'total'             => $total,
            'pagado_acumulado'  => round($pagadoAcumulado, 2),
            'saldo_pendiente'   => round(max(0, $total - $pagadoAcumulado), 2),
            'total_pendiente'   => round($totalPendiente, 2),
            'total_pagado_platos' => round($totalPagadoPlatos, 2),
            'platos_pendientes' => $platosPendientes,
            'platos_pagados'    => $platosPagados,
        ]);
    }

    public function cobrar(Request $request, Mesa $mesa)
    {
        $request->validate([
            'metodo_pago'       => 'required|in:efectivo,tarjeta,yape,plin',
            'monto_pagado'      => 'required|numeric|min:0',
            'notas'             => 'nullable|string',
            'tipo_comprobante'  => 'nullable|in:boleta,factura,ninguno',
            'partes_total'      => 'nullable|integer|min:1',
            'parte_numero'      => 'nullable|integer|min:1',
        ]);

        $pedidos = Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->get();

        $total = $pedidos->sum('total');

        $pagadoPrevio = (float) CajaRestaurante::where('mesa_id', $mesa->id)
            ->whereNotNull('cuenta_total')
            ->where('cuenta_total', $total)
            ->whereHas('mesa', function ($q) {
                $q->where('estado', 'ocupada');
            })
            ->sum('monto_pagado');

        $partesTotal = (int) ($request->partes_total ?? 1);
        $parteNumero = (int) ($request->parte_numero ?? 1);

        $montoPagado     = (float) $request->monto_pagado;
        $pagadoAcumulado = $pagadoPrevio + $montoPagado;
        $saldoPendiente  = round($total - $pagadoAcumulado, 2);

        $cuentaSaldada = $pagadoAcumulado >= ($total - 0.01);

        $vuelto = $cuentaSaldada ? round(max(0, $pagadoAcumulado - $total), 2) : 0;

        $caja = CajaRestaurante::create([
            'empresa_id'       => auth()->user()->empresa_id,
            'mesa_id'          => $mesa->id,
            'user_id'          => auth()->id(),
            'total'            => $total,
            'monto_pagado'     => $montoPagado,
            'vuelto'           => $vuelto,
            'metodo_pago'      => $request->metodo_pago,
            'tipo_comprobante' => $request->tipo_comprobante ?? 'ninguno',
            'notas'            => $request->notas,
            'partes_total'     => $partesTotal,
            'parte_numero'     => $parteNumero,
            'cuenta_total'     => $total,
            'pagado_acumulado' => round($pagadoAcumulado, 2),
        ]);

        $sesion = SesionCaja::where('estado', 'abierta')->first();
        if ($sesion) {
            $concepto = 'Cobro Mesa ' . $mesa->numero . ' (' . $request->metodo_pago . ')';
            if ($partesTotal > 1) {
                $concepto .= " - parte {$parteNumero}/{$partesTotal}";
            }
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => $concepto,
                'referencia_id'=> $caja->id,
                'monto'        => $montoPagado,
                'observaciones'=> $request->notas ?? null,
            ]);
        }

        if (!$cuentaSaldada) {
            return redirect()->route('caja-restaurante.show', $mesa)
                ->with('success', "Parte {$parteNumero}/{$partesTotal} cobrada (S/ " . number_format($montoPagado, 2) . "). Falta S/ " . number_format($saldoPendiente, 2));
        }

        $pedidosItems = \App\Models\PedidoDetalle::whereIn('pedido_id',
            Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
                ->whereIn('estado', ['enviado', 'listo'])
                ->pluck('id')
        )->where('anulado', false)->get();

        foreach ($pedidosItems as $item) {
            $recetas = Receta::with('insumo')
                ->where('menu_producto_id', $item->menu_producto_id)
                ->get();

            foreach ($recetas as $receta) {
                $insumo = $receta->insumo;
                $cantidadTotal = $receta->cantidad * $item->cantidad;
                $stockAnterior = $insumo->stock_actual;
                $stockNuevo = max(0, $stockAnterior - $cantidadTotal);

                $insumo->update(['stock_actual' => $stockNuevo]);

                InsumoMovimiento::create([
                    'insumo_id'      => $insumo->id,
                    'user_id'        => auth()->id(),
                    'tipo'           => 'salida',
                    'cantidad'       => $cantidadTotal,
                    'costo_unitario' => $insumo->precio_promedio,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockNuevo,
                    'motivo'         => 'Venta Mesa ' . $mesa->numero,
                ]);
            }
        }

        Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->update(['estado' => 'cerrado', 'caja_restaurante_id' => $caja->id]);

        Mesa::whereIn('id', $this->idsGrupoMesa($mesa))->update(['estado' => 'libre', 'mesa_principal_id' => null]);

        $tipo = $request->tipo_comprobante ?? 'ninguno';

        if ($tipo === 'ninguno') {
            return redirect()->route('mesas.index')
                ->with('success', "Mesa {$mesa->numero} cobrada. Vuelto: S/ {$vuelto}");
        }

        // Si vienen datos del cliente, emitir comprobante directo sin redirigir
        $clienteDoc  = $request->cliente_documento ?? '';
        $clienteNombre = $request->cliente_nombre ?? '';
        $clienteEmail  = $request->cliente_email ?? '';

        if ($clienteDoc && $clienteNombre) {
            $empresa   = auth()->user()->empresa;
            $exonerada = $empresa->zona_exonerada ?? false;
            $totalMonto = round(floatval($caja->total), 2);

            if ($exonerada) { $gravada = 0; $igv = 0; }
            else { $gravada = round($totalMonto / 1.18, 2); $igv = round($totalMonto - $gravada, 2); }
            $baseImponible = $exonerada ? $totalMonto : $gravada;

            $tipoComp  = $tipo === 'factura' ? '01' : '03';
            if ($tipo === 'factura') {
                $serie       = $empresa->serie_factura ?? 'F001';
                $correlativo = ($empresa->ultimo_num_factura ?? 0) + 1;
                $empresa->increment('ultimo_num_factura');
            } else {
                $serie       = $empresa->serie_boleta ?? 'B001';
                $correlativo = ($empresa->ultimo_num_boleta ?? 0) + 1;
                $empresa->increment('ultimo_num_boleta');
            }

            $fileName = $empresa->ruc . '-' . $tipoComp . '-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);
            $tipoDocCliente = $tipo === 'factura' ? '6' : ($request->cliente_tipo_documento ?? '1');

            $valUnit = $exonerada ? $totalMonto : round($totalMonto / 1.18, 4);
            $igvItem = $exonerada ? 0 : round($totalMonto - $valUnit, 2);

            $lineas = [[
                'cbc:ID'                  => ['_text' => '1'],
                'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => '1'],
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                'cac:PricingReference'    => ['cac:AlternativeConditionPrice' => [
                    'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
                    'cbc:PriceTypeCode' => ['_text' => '01'],
                ]],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                    'cac:TaxSubtotal' => [[
                        'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                        'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                        'cac:TaxCategory'   => [
                            'cbc:Percent'                => ['_text' => $exonerada ? '0' : '18'],
                            'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                            'cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']],
                        ],
                    ]],
                ],
                'cac:Item'  => ['cbc:Description' => ['_text' => 'Consumo Mesa ' . $mesa->numero], 'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']]],
                'cac:Price' => ['cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit]],
            ]];

            $documentBody = [
                'cbc:UBLVersionID'         => ['_text' => '2.1'],
                'cbc:CustomizationID'      => ['_text' => '2.0'],
                'cbc:ID'                   => ['_text' => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT)],
                'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
                'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $tipoComp],
                'cbc:Note'                 => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => strtoupper($this->numeroALetras($totalMonto))],
                'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
                'cac:PaymentTerms'         => ['cbc:ID' => ['_text' => 'FormaPago'], 'cbc:PaymentMeansID' => ['_text' => 'Contado']],
                'cac:AccountingSupplierParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                    'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                    'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => $empresa->razon_social], 'cac:RegistrationAddress' => ['cbc:AddressTypeCode' => ['_text' => '0000'], 'cac:AddressLine' => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']]]],
                ]],
                'cac:AccountingCustomerParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $tipoDocCliente], '_text' => $clienteDoc]],
                    'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($clienteNombre)]],
                ]],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxSubtotal' => [[
                        'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                        'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                        'cac:TaxCategory'   => ['cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']]],
                    ]],
                ],
                'cac:LegalMonetaryTotal' => [
                    'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                    'cbc:TaxInclusiveAmount'  => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
                    'cbc:PayableAmount'       => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
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

                $data      = $response->json();
                $estadosOk = ['PENDIENTE', 'aceptado', 'ACEPTADO'];
                $aceptada  = $response->successful() && isset($data['sunatResponse']);
                $pendiente = $response->successful() && isset($data['status']) && in_array($data['status'], $estadosOk);
                $pdfUrl    = $data['sunatResponse']['enlace_del_pdf'] ?? null;

                $comprobante = \App\Models\ComprobanteSunat::create([
                    'empresa_id'               => $empresa->id,
                    'caja_restaurante_id'      => $caja->id,
                    'tipo_comprobante'         => $tipoComp,
                    'serie'                    => $serie,
                    'numero'                   => $correlativo,
                    'fecha_emision'            => now()->toDateString(),
                    'cliente_tipo_documento'   => $tipoDocCliente,
                    'cliente_numero_documento' => $clienteDoc,
                    'cliente_nombre'           => strtoupper($clienteNombre),
                    'cliente_email'            => $clienteEmail,
                    'total_gravada'            => $gravada,
                    'total_igv'                => $igv,
                    'total'                    => $totalMonto,
                    'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                    'sunat_descripcion'        => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                    'enlace_pdf'               => $pdfUrl,
                    'estado'                   => $aceptada ? 'aceptado' : ($pendiente ? 'aceptado' : 'rechazado'),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error emitir comprobante cobro: ' . $e->getMessage());
            }

            return redirect()->route('comprobantes.show', $comprobante)
                ->with('success', "Mesa {$mesa->numero} cobrada y comprobante emitido.")
                ->with('imprimir', true);
        }

        // Sin datos de cliente → redirigir a página de comprobante
        return redirect()->route('comprobantes.crear', $caja)
            ->with('success', "Mesa {$mesa->numero} cobrada. Vuelto: S/ {$vuelto}")
            ->with('tipo_comprobante', $tipo);
    }

    private function numeroALetras($numero)
    {
        $entero  = (int)$numero;
        $decimal = round(($numero - $entero) * 100);
        return $this->enLetras($entero) . ' CON ' . str_pad($decimal, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
    }

    private function enLetras($n)
    {
        $u = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE','DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE'];
        $d = ['','','VEINTE','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
        $c = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS','SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];
        if ($n == 0) return 'CERO';
        if ($n == 100) return 'CIEN';
        if ($n < 16) return $u[$n];
        if ($n < 20) return 'DIECI' . $u[$n - 10];
        if ($n == 20) return 'VEINTE';
        if ($n < 30) return 'VEINTI' . $u[$n - 20];
        if ($n < 100) return $d[intdiv($n,10)] . ($n%10 ? ' Y ' . $u[$n%10] : '');
        if ($n < 1000) return $c[intdiv($n,100)] . ($n%100 ? ' ' . $this->enLetras($n%100) : '');
        if ($n < 2000) return 'MIL' . ($n%1000 ? ' ' . $this->enLetras($n%1000) : '');
        if ($n < 1000000) return $this->enLetras(intdiv($n,1000)) . ' MIL' . ($n%1000 ? ' ' . $this->enLetras($n%1000) : '');
        return (string)$n;
    }

    // Cobrar solo los platos seleccionados (division por platos)
    public function cobrarPlatos(Request $request, Mesa $mesa)
    {
        $request->validate([
            'metodo_pago'      => 'required|in:efectivo,tarjeta,yape,plin',
            'detalle_ids'      => 'required|array|min:1',
            'detalle_ids.*'    => 'integer',
            'monto_pagado'     => 'required|numeric|min:0',
            'notas'            => 'nullable|string',
            'tipo_comprobante' => 'nullable|in:boleta,factura,ninguno',
        ]);

        $pedidoIds = Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->pluck('id');

        $detalles = \App\Models\PedidoDetalle::whereIn('id', $request->detalle_ids)
            ->whereIn('pedido_id', $pedidoIds)
            ->where('pagado', false)
            ->where('anulado', false)
            ->get();

        if ($detalles->isEmpty()) {
            return redirect()->route('caja-restaurante.show', $mesa)
                ->with('error', 'Los platos seleccionados ya estan pagados o no son validos.');
        }

        $subtotal = $detalles->sum('subtotal');
        $vuelto   = max(0, $request->monto_pagado - $subtotal);

        $caja = CajaRestaurante::create([
            'empresa_id'       => auth()->user()->empresa_id,
            'mesa_id'          => $mesa->id,
            'user_id'          => auth()->id(),
            'total'            => $subtotal,
            'monto_pagado'     => $request->monto_pagado,
            'vuelto'           => $vuelto,
            'metodo_pago'      => $request->metodo_pago,
            'tipo_comprobante' => $request->tipo_comprobante ?? 'ninguno',
            'notas'            => $request->notas,
            'partes_total'     => 0,
            'parte_numero'     => 0,
            'cuenta_total'     => $subtotal,
            'pagado_acumulado' => $subtotal,
        ]);

        \App\Models\PedidoDetalle::whereIn('id', $detalles->pluck('id'))
            ->update(['pagado' => true, 'caja_detalle_id' => $caja->id]);

        $sesion = SesionCaja::where('estado', 'abierta')->first();
        if ($sesion) {
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => 'Cobro Mesa ' . $mesa->numero . ' (' . $request->metodo_pago . ') - por platos',
                'referencia_id'=> $caja->id,
                'monto'        => $subtotal,
                'observaciones'=> $request->notas ?? null,
            ]);
        }

        foreach ($detalles as $item) {
            $recetas = Receta::with('insumo')
                ->where('menu_producto_id', $item->menu_producto_id)
                ->get();

            foreach ($recetas as $receta) {
                $insumo = $receta->insumo;
                $cantidadTotal = $receta->cantidad * $item->cantidad;
                $stockAnterior = $insumo->stock_actual;
                $stockNuevo = max(0, $stockAnterior - $cantidadTotal);

                $insumo->update(['stock_actual' => $stockNuevo]);

                InsumoMovimiento::create([
                    'insumo_id'      => $insumo->id,
                    'user_id'        => auth()->id(),
                    'tipo'           => 'salida',
                    'cantidad'       => $cantidadTotal,
                    'costo_unitario' => $insumo->precio_promedio,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockNuevo,
                    'motivo'         => 'Venta Mesa ' . $mesa->numero . ' (por platos)',
                ]);
            }
        }

        $platosPendientes = \App\Models\PedidoDetalle::whereIn('pedido_id', $pedidoIds)
            ->where('pagado', false)
            ->where('anulado', false)
            ->count();

        if ($platosPendientes > 0) {
            return redirect()->route('caja-restaurante.show', $mesa)
                ->with('success', "Platos cobrados (S/ " . number_format($subtotal, 2) . "). Faltan {$platosPendientes} plato(s) por pagar.");
        }

        Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->update(['estado' => 'cerrado', 'caja_restaurante_id' => $caja->id]);

        Mesa::whereIn('id', $this->idsGrupoMesa($mesa))->update(['estado' => 'libre', 'mesa_principal_id' => null]);

        $tipo = $request->tipo_comprobante ?? 'boleta';

        if ($tipo === 'ninguno') {
            return redirect()->route('mesas.index')
                ->with('success', "Mesa {$mesa->numero} cobrada completa.");
        }

        return redirect()->route('comprobantes.crear', $caja)
            ->with('success', "Mesa {$mesa->numero} cobrada completa.")
            ->with('tipo_comprobante', $tipo);
    }

}
