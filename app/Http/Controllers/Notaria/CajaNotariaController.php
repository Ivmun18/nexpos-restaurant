<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoPago;
use App\Models\SesionCaja;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CajaNotariaController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $buscar = request()->get('buscar', '');

        // Expedientes con saldo pendiente
        $query = ActoNotarial::with(['cliente', 'pagos'])
            ->where('empresa_id', $empresaId)
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->where('estado', '!=', 'cancelado');

        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('numero_expediente', 'like', "%{$buscar}%")
                  ->orWhere('asunto', 'like', "%{$buscar}%")
                  ->orWhere('partes_intervinientes', 'like', "%{$buscar}%")
                  ->orWhereHas('cliente', function($q2) use ($buscar) {
                      $q2->where('razon_social', 'like', "%{$buscar}%")
                         ->orWhere('numero_documento', 'like', "%{$buscar}%");
                  });
            });
        }

        $pendientes = $query->orderBy('fecha_ingreso', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'                 => $a->id,
                'numero_expediente'  => $a->numero_expediente,
                'tipo_acto'          => $a->tipo_acto,
                'asunto'             => $a->asunto,
                'partes_intervinientes' => $a->partes_intervinientes,
                'monto_cobrar'       => $a->monto_cobrar,
                'monto_pagado'       => $a->monto_pagado,
                'saldo'              => round($a->monto_cobrar - $a->monto_pagado, 2),
                'estado_pago'        => $a->estado_pago,
                'estado'             => $a->estado,
                'fecha_ingreso'      => $a->fecha_ingreso,
                'pagos'              => $a->pagos,
            ]);

        $sesionAbierta = SesionCaja::where('estado', 'abierta')->exists();
        $sesionActual  = SesionCaja::with('movimientos')
            ->where('estado', 'abierta')
            ->first();

        // Calcular totales de la sesión actual
        $resumenCaja = null;
        if ($sesionActual) {
            $ingresos = $sesionActual->movimientos->where('tipo', 'ingreso')->sum('monto');
            $egresos  = $sesionActual->movimientos->where('tipo', 'egreso')->sum('monto');
            $resumenCaja = [
                'id'             => $sesionActual->id,
                'apertura'       => $sesionActual->created_at,
                'fondo_inicial'  => $sesionActual->monto_apertura,
                'ingresos'       => round($ingresos, 2),
                'egresos'        => round($egresos, 2),
                'saldo_sistema'  => round($sesionActual->monto_apertura + $ingresos - $egresos, 2),
            ];
        }

        // Expedientes pagados hoy para emitir comprobante
        $pagadosHoy = ActoNotarial::with(['cliente', 'pagos'])
            ->where('empresa_id', $empresaId)
            ->where('estado_pago', 'pagado')
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'                    => $a->id,
                'numero_expediente'     => $a->numero_expediente,
                'tipo_acto'             => $a->tipo_acto,
                'asunto'                => $a->asunto,
                'partes_intervinientes' => $a->partes_intervinientes,
                'monto_cobrar'          => $a->monto_cobrar,
                'monto_pagado'          => $a->monto_pagado,
                'saldo'                 => 0,
                'estado_pago'           => $a->estado_pago,
            ]);

        return Inertia::render('Notaria/Caja/Index', [
            'pendientes'    => $pendientes,
            'pagadosHoy'    => $pagadosHoy,
            'sesionAbierta' => $sesionAbierta,
            'resumenCaja'   => $resumenCaja,
            'filtros'       => ['buscar' => $buscar],
        ]);
    }

    public function cobrar(Request $request, ActoNotarial $acto)
    {
        // Verificar que hay caja abierta
        if (!SesionCaja::where('estado', 'abierta')->exists()) {
            return back()->with('error', 'Debe abrir la caja antes de registrar cobros.');
        }

        $request->validate([
            'monto'      => 'required|numeric|min:0.01',
            'metodo_pago'=> 'required|in:efectivo,yape,plin,tarjeta,transferencia',
            'tipo'       => 'required|in:adelanto,pago_parcial,pago_final',
            'referencia' => 'nullable|string|max:100',
        ]);

        $nuevoPagado = round($acto->monto_pagado + $request->monto, 2);
        $estadoPago  = $nuevoPagado >= $acto->monto_cobrar ? 'pagado' : 'parcial';

        // Registrar pago detallado
        ActoPago::create([
            'acto_id'      => $acto->id,
            'usuario_id'   => auth()->id(),
            'monto'        => $request->monto,
            'metodo_pago'  => $request->metodo_pago,
            'tipo'         => $request->tipo,
            'referencia'   => $request->referencia,
            'observaciones'=> $request->observaciones,
        ]);

        // Actualizar monto pagado en el acto
        $acto->update([
            'monto_pagado' => $nuevoPagado,
            'estado_pago'  => $estadoPago,
        ]);

        // Si pago final, marcar como finalizado
        if ($estadoPago === 'pagado' && $acto->estado === 'en_proceso') {
            $acto->update(['estado' => 'finalizado']);
        }

        // Registrar en caja si hay sesión abierta
        $sesion = SesionCaja::where('estado', 'abierta')->first();
        if ($sesion) {
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => ucfirst($request->tipo) . ' ' . $acto->numero_expediente . ' (' . $request->metodo_pago . ')',
                'referencia_id'=> $acto->id,
                'monto'        => $request->monto,
            ]);
        }

        // Emitir comprobante electrónico si se llenaron los datos
        if ($request->tipo_comprobante && $request->cliente_nombre) {
            try {
                $empresa = auth()->user()->empresa;
                $tipoComp = $request->tipo_comprobante; // '01' o '03'

                if ($tipoComp === '01') {
                    $serie       = $empresa->serie_factura ?? 'F001';
                    $correlativo = ($empresa->ultimo_num_factura ?? 0) + 1;
                    $empresa->increment('ultimo_num_factura');
                } else {
                    $serie       = $empresa->serie_boleta ?? 'B001';
                    $correlativo = ($empresa->ultimo_num_boleta ?? 0) + 1;
                    $empresa->increment('ultimo_num_boleta');
                }

                $montoComp     = (float) $request->monto;
                $exonerada     = $empresa->zona_exonerada;
                $baseImponible = $exonerada ? $montoComp : round($montoComp / 1.18, 2);
                $igv           = $exonerada ? 0 : round($montoComp - $baseImponible, 2);
                $fileName      = $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

                $valUnit = $exonerada ? $montoComp : round($montoComp / 1.18, 4);
                $igvItem = $exonerada ? 0 : round($montoComp - $valUnit, 2);

                $lineas = [[
                    'cbc:ID'                  => ['_text' => '1'],
                    'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => '1'],
                    'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                    'cac:TaxTotal' => [
                        'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                        'cac:TaxSubtotal' => [[
                            'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                            'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                            'cac:TaxCategory'   => [
                                'cbc:ID'       => ['_attributes' => ['schemeID'=>'UN/ECE 5305','schemeName'=>'Tax Category Identifier','schemeAgencyName'=>'United Nations Economic Commission for Europe'], '_text' => $exonerada ? 'E' : 'S'],
                                'cbc:Percent'  => ['_text' => $exonerada ? '0' : '18'],
                                'cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']],
                            ],
                        ]],
                    ],
                    'cac:Item' => [
                        'cbc:Description' => ['_text' => $acto->asunto ?? 'Servicio notarial'],
                        'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => $acto->numero_expediente ?? 'S/C']],
                    ],
                    'cac:Price' => [
                        'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                    ],
                ]];

                $documentBody = [
                    'cbc:UBLVersionID'         => ['_text' => '2.1'],
                    'cbc:CustomizationID'      => ['_text' => '2.0'],
                    'cbc:ID'                   => ['_text' => $fileName],
                    'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
                    'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $tipoComp],
                    'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
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
                            'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $request->cliente_tipo_documento ?? '1'], '_text' => $request->cliente_numero_documento]],
                            'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($request->cliente_nombre)]],
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
                        'cbc:TaxInclusiveAmount'  => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $montoComp],
                        'cbc:PayableAmount'       => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $montoComp],
                    ],
                    'cac:InvoiceLine' => $lineas,
                ];

                $response = \Illuminate\Support\Facades\Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(30)
                    ->post('https://back.apisunat.com/personas/v1/sendBill', [
                        'personaId'    => $empresa->apisunat_ruc,
                        'personaToken' => $empresa->apisunat_token,
                        'fileName'     => $fileName,
                        'documentBody' => $documentBody,
                    ]);

                $data     = $response->json();
                $aceptada = $response->successful() && isset($data['sunatResponse']);

                \DB::table('comprobantes_sunat')->insert([
                    'empresa_id'               => $empresa->id,
                    'tipo_comprobante'         => $tipoComp,
                    'serie'                    => $serie,
                    'numero'                   => $correlativo,
                    'fecha_emision'            => now()->toDateString(),
                    'cliente_tipo_documento'   => $request->cliente_tipo_documento ?? '1',
                    'cliente_numero_documento' => $request->cliente_numero_documento,
                    'cliente_nombre'           => strtoupper($request->cliente_nombre),
                    'cliente_email'            => $request->cliente_email ?? '',
                    'total_gravada'            => $baseImponible,
                    'total_igv'                => $igv,
                    'total'                    => $montoComp,
                    'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                    'sunat_descripcion'        => $aceptada ? 'Aceptada' : json_encode($data),
                    'estado'                   => $aceptada ? 'aceptado' : 'emitido',
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);

                $compMsg = $aceptada
                    ? ' | Comprobante ' . $fileName . ' emitido ✅'
                    : ' | Comprobante guardado (pendiente SUNAT)';

                return back()->with('success', 'Pago de S/ ' . $request->monto . ' registrado.' . $compMsg);

            } catch (\Exception $e) {
                \Log::error('Error emitir comprobante en cobro: ' . $e->getMessage());
                return back()->with('success', 'Pago de S/ ' . $request->monto . ' registrado. (Error al emitir comprobante: ' . $e->getMessage() . ')');
            }
        }

        return back()->with('success', 'Pago de S/ ' . $request->monto . ' registrado correctamente.');
    }

    public function abrir(\Illuminate\Http\Request $request)
    {
        $request->validate(['monto_apertura' => 'required|numeric|min:0']);

        if (SesionCaja::where('estado', 'abierta')->exists()) {
            return back()->with('error', 'Ya hay una caja abierta.');
        }

        $sesion = SesionCaja::create([
            'usuario_id'     => auth()->id(),
            'monto_apertura' => $request->monto_apertura,
            'estado'         => 'abierta',
        ]);

        CajaMovimiento::create([
            'sesion_id'  => $sesion->id,
            'usuario_id' => auth()->id(),
            'tipo'       => 'ingreso',
            'concepto'   => 'Apertura de caja',
            'monto'      => $request->monto_apertura,
        ]);

        return back()->with('success', 'Caja abierta con S/ ' . $request->monto_apertura);
    }

    public function cerrar(\Illuminate\Http\Request $request)
    {
        $sesion = SesionCaja::where('estado', 'abierta')->with('movimientos')->first();

        if (!$sesion) {
            return back()->with('error', 'No hay caja abierta.');
        }

        $ingresos     = $sesion->movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresos      = $sesion->movimientos->where('tipo', 'egreso')->sum('monto');
        $montoSistema = round($sesion->monto_apertura + $ingresos - $egresos, 2);
        $montoReal    = $request->monto_real ?? $montoSistema;
        $diferencia   = round($montoReal - $montoSistema, 2);

        $sesion->update([
            'fecha_cierre'         => now(),
            'monto_cierre_sistema' => $montoSistema,
            'monto_cierre_real'    => $montoReal,
            'diferencia'           => $diferencia,
            'estado'               => 'cerrada',
            'observaciones'        => $request->observaciones,
        ]);

        return back()->with('success', 'Caja cerrada. Saldo sistema: S/ ' . $montoSistema);
    }
}
