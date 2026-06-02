<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ComprobantesNotariaController extends Controller
{
    public function emitir(Request $request, ActoNotarial $acto)
    {
        $request->validate([
            'tipo_comprobante'         => 'required|in:01,03',
            'cliente_tipo_documento'   => 'required|in:0,1,6',
            'cliente_numero_documento' => 'required|string',
            'cliente_nombre'           => 'required|string',
            'cliente_email'            => 'nullable|email',
        ]);

        $empresa   = Empresa::find($acto->empresa_id);
        $exonerada = $empresa->zona_exonerada;

        if ($request->tipo_comprobante === '01') {
            $serie       = $empresa->serie_factura ?? 'F001';
            $correlativo = ($empresa->ultimo_num_factura ?? 0) + 1;
            $empresa->increment('ultimo_num_factura');
        } else {
            $serie       = $empresa->serie_boleta ?? 'B001';
            $correlativo = ($empresa->ultimo_num_boleta ?? 0) + 1;
            $empresa->increment('ultimo_num_boleta');
        }

        $total     = round(floatval($acto->monto_cobrar), 2);
        $gravada   = $exonerada ? 0 : round($total / 1.18, 2);
        $igv       = $exonerada ? 0 : round($total - $gravada, 2);
        $baseImponible = $exonerada ? $total : $gravada;
        $fileName = $empresa->ruc . '-' . $request->tipo_comprobante . '-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

        // Desglosar: servicio notarial + huella digital (1.50 solo si total >= 10)
        $huella       = $total >= 10 ? 1.50 : 0;
        $montoServicio = round($total - $huella, 2);

        $lineas = [];
        $items = array_filter([
            ['desc' => $acto->asunto, 'codigo' => $acto->numero_expediente ?? 'S/C', 'precio' => $montoServicio],
            ...($huella > 0 ? [['desc' => 'Uso biométrico', 'codigo' => 'UB', 'precio' => $huella]] : []),
        ], fn($item) => $item['precio'] > 0);
        if (empty($items)) {
            $items = [['desc' => $acto->asunto, 'codigo' => $acto->numero_expediente ?? 'S/C', 'precio' => $total]];
        }

        foreach ($items as $idx => $item) {
            $precioItem = round($item['precio'], 2);
            $valUnit    = $exonerada ? $precioItem : round($precioItem / 1.18, 4);
            $igvItem    = $exonerada ? 0 : round($precioItem - $valUnit, 2);

            $lineas[] = [
                'cbc:ID'                  => ['_text' => (string)($idx + 1)],
                'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => '1'],
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                'cac:PricingReference' => [
                    'cac:AlternativeConditionPrice' => [
                        'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $precioItem],
                        'cbc:PriceTypeCode' => ['_text' => '01'],
                    ],
                ],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                    'cac:TaxSubtotal' => [[
                        'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                        'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                        'cac:TaxCategory'   => [
                            'cbc:ID'      => ['_text' => 'S'],
                            'cbc:Percent' => ['_text' => $exonerada ? '0' : '18'],
                            'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                            'cac:TaxScheme' => ['cbc:ID' => ['_text' => '1000'], 'cbc:Name' => ['_text' => 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']],
                        ],
                    ]],
                ],
                'cac:Item' => [
                    'cbc:Description' => ['_text' => $item['desc']],
                    'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => $item['codigo']]],
                ],
                'cac:Price' => [
                    'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                ],
            ];
        }

        $documentBody = [
            'cbc:UBLVersionID'         => ['_text' => '2.1'],
            'cbc:CustomizationID'      => ['_text' => '2.0'],
            'cbc:ID'                   => ['_text' => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT)],
            'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
            'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $request->tipo_comprobante],
            'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
            'cac:AccountingSupplierParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                'cac:PostalAddress'       => ['cbc:AddressTypeCode' => ['_text' => '0000'], 'cac:AddressLine' => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']]],
            ]],
            'cac:AccountingCustomerParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $request->cliente_tipo_documento], '_text' => $request->cliente_numero_documento]],
                'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($request->cliente_nombre)]],
            ]],
            'cac:TaxTotal' => [
                'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxCategory'   => [
                        'cbc:ID'      => ['_text' => 'S'],
                        'cbc:Percent' => ['_text' => $exonerada ? '0' : '18'],
                        'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                        'cac:TaxScheme' => ['cbc:ID' => ['_text' => '1000'], 'cbc:Name' => ['_text' => 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']],
                    ],
                ]],
            ],
            'cac:LegalMonetaryTotal' => [
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                'cbc:TaxInclusiveAmount'  => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                'cbc:PayableAmount'       => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
            ],
            'cac:InvoiceLine' => $lineas,
        ];

        \Log::info('emitir fileName: ' . $fileName . ' ruc: ' . $empresa->ruc . ' token: ' . substr($empresa->apisunat_token,0,15));
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
            \Log::info("emitir response: " . json_encode($data));
            $aceptada  = $response->successful() && isset($data['sunatResponse']);
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';
            $pdfUrl    = $data['sunatResponse']['enlace_del_pdf'] ?? null;

            \DB::table('comprobantes_sunat')->insert([
                'empresa_id'               => $empresa->id,
                'acto_id'                  => $acto->id,
                'tipo_comprobante'         => $request->tipo_comprobante,
                'serie'                    => $serie,
                'numero'                   => $correlativo,
                'fecha_emision'            => now()->toDateString(),
                'cliente_tipo_documento'   => $request->cliente_tipo_documento,
                'cliente_numero_documento' => $request->cliente_numero_documento,
                'cliente_nombre'           => strtoupper($request->cliente_nombre),
                'cliente_email'            => $request->cliente_email ?? '',
                'total_gravada'            => $baseImponible,
                'total_igv'                => $igv,
                'total'                    => $total,
                'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                'sunat_descripcion'        => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                'enlace_xml'               => $pendiente && isset($data['xml']) ? $data['xml'] : null,
                'enlace_pdf'               => $pdfUrl,
                'enlace_cdr'               => isset($request->items[0]['descripcion']) ? $request->items[0]['descripcion'] : null,
                'estado'                   => $aceptada ? 'aceptado' : ($pendiente ? 'aceptado' : 'rechazado'),
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            $comprobanteId = \DB::getPdo()->lastInsertId();

            // Guardar cliente automáticamente si no existe
            if ($request->cliente_numero_documento && $request->cliente_numero_documento !== '00000000') {
                \DB::table('clientes')->updateOrInsert(
                    ['empresa_id' => $empresa->id, 'numero_documento' => $request->cliente_numero_documento],
                    [
                        'tipo_documento'  => $request->cliente_tipo_documento ?? '1',
                        'razon_social'    => strtoupper($request->cliente_nombre),
                        'email'           => $request->cliente_email ?? null,
                        'updated_at'      => now(),
                        'created_at'      => now(),
                    ]
                );
            }

            $sesion = \DB::table('sesiones_caja')->where('estado', 'abierta')->first();
            if ($sesion) {
                \DB::table('caja_movimientos')->insert([
                    'sesion_id'  => $sesion->id,
                    'usuario_id' => auth()->id(),
                    'tipo'       => 'ingreso',
                    'concepto'   => 'Expediente ' . $acto->numero_expediente . ' - ' . $fileName,
                    'monto'      => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => $aceptada,
                'mensaje' => $aceptada ? $fileName . ' emitida correctamente' : 'Rechazada: ' . json_encode($data),
                'pdf'     => '/notaria/comprobantes/' . $comprobanteId . '/recibo-ticket',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error emitir comprobante notaria: ' . $e->getMessage());
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function ventaDirecta(Request $request)
    {
        \Log::info('ventaDirecta llamado', $request->all());

        $request->validate([
            'tipo_comprobante'         => 'required|in:01,03',
            'cliente_tipo_documento'   => 'required',
            'cliente_numero_documento' => 'required',
            'cliente_nombre'           => 'required|string',
            'cliente_email'            => 'nullable|email',
            'items'                    => 'required|array|min:1',
            'items.*.descripcion'      => 'required|string',
            'items.*.precio'           => 'required|numeric|min:0.01',
            'metodo_pago'              => 'required|in:efectivo,yape,plin,transferencia,tarjeta',
        ]);

        $empresa = auth()->user()->empresa;

        // Correlativo
        if ($request->tipo_comprobante === '01') {
            $serie       = $empresa->serie_factura ?? 'F001';
            $correlativo = ($empresa->ultimo_num_factura ?? 0) + 1;
            $empresa->increment('ultimo_num_factura');
        } else {
            $serie       = $empresa->serie_boleta ?? 'B001';
            $correlativo = ($empresa->ultimo_num_boleta ?? 0) + 1;
            $empresa->increment('ultimo_num_boleta');
        }

        // Totales
        $total      = round(collect($request->items)->sum('precio'), 2);
        $exonerada  = $empresa->zona_exonerada;
        if ($exonerada) {
            $gravada = 0;
            $igv     = 0;
            $baseImponible = $total;
        } else {
            $gravada = round($total / 1.18, 2);
            $igv     = round($total - $gravada, 2);
            $baseImponible = $gravada;
        }

        $fileName = $empresa->ruc . '-' . $request->tipo_comprobante . '-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

        // Construir items UBL
        $lineas = [];
        foreach ($request->items as $idx => $item) {
            $precioItem = round(floatval($item['precio']), 2);
            $valUnit    = $exonerada ? $precioItem : round($precioItem / 1.18, 4);
            $igvItem    = $exonerada ? 0 : round($precioItem - $valUnit, 2);
            $lineas[] = [
                'cbc:ID'                  => ['_text' => (string)($idx + 1)],
                'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => '1'],
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                'cac:PricingReference' => [
                    'cac:AlternativeConditionPrice' => [
                        'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $precioItem],
                        'cbc:PriceTypeCode' => ['_text' => '01'],
                    ],
                ],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
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
                'cac:Item' => [
                    'cbc:Description' => ['_text' => $item['descripcion']],
                    'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']],
                ],
                'cac:Price' => [
                    'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                ],
            ];
        }

        $documentBody = [
            'cbc:UBLVersionID'         => ['_text' => '2.1'],
            'cbc:CustomizationID'      => ['_text' => '2.0'],
            'cbc:ID'                   => ['_text' => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT)],
            'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
            'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $request->tipo_comprobante],
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
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $request->cliente_tipo_documento], '_text' => $request->cliente_numero_documento]],
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
                'cbc:TaxInclusiveAmount'  => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                'cbc:PayableAmount'       => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
            ],
            'cac:InvoiceLine' => $lineas,
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post('https://back.apisunat.com/personas/v1/sendBill', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                    'documentBody' => $documentBody,
                ]);

            $data = $response->json();
            \Log::info('ApiSunat ventaDirecta response: ' . json_encode($data));

            $aceptada  = $response->successful() && isset($data['sunatResponse']);
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';
            $pdfUrl    = null;

            // Guardar comprobante
            \DB::table('comprobantes_sunat')->insert([
                'empresa_id'               => $empresa->id,
                'tipo_comprobante'         => $request->tipo_comprobante,
                'serie'                    => $serie,
                'numero'                   => $correlativo,
                'fecha_emision'            => now()->toDateString(),
                'cliente_tipo_documento'   => $request->cliente_tipo_documento,
                'cliente_numero_documento' => $request->cliente_numero_documento,
                'cliente_nombre'           => strtoupper($request->cliente_nombre),
                'cliente_email'            => $request->cliente_email ?? '',
                'total_gravada'            => $gravada,
                'total_igv'                => $igv,
                'total'                    => $total,
                'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                'sunat_descripcion'        => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                'enlace_xml'               => $pendiente && isset($data['xml']) ? $data['xml'] : null,
                'enlace_pdf'               => $pdfUrl,
                'enlace_cdr'               => isset($request->items[0]['descripcion']) ? $request->items[0]['descripcion'] : null,
                'estado'                   => $aceptada ? 'aceptado' : ($pendiente ? 'aceptado' : 'rechazado'),
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
            $comprobanteId = \DB::getPdo()->lastInsertId();

            // Guardar cliente automáticamente si no existe
            if ($request->cliente_numero_documento && $request->cliente_numero_documento !== '00000000') {
                \DB::table('clientes')->updateOrInsert(
                    ['empresa_id' => $empresa->id, 'numero_documento' => $request->cliente_numero_documento],
                    [
                        'tipo_documento'  => $request->cliente_tipo_documento ?? '1',
                        'razon_social'    => strtoupper($request->cliente_nombre),
                        'email'           => $request->cliente_email ?? null,
                        'updated_at'      => now(),
                        'created_at'      => now(),
                    ]
                );
            }

            // Registrar en caja si hay sesión abierta
            $sesion = \DB::table('sesiones_caja')->where('estado', 'abierta')->first();
            if ($sesion) {
                \DB::table('caja_movimientos')->insert([
                    'sesion_id'  => $sesion->id,
                    'usuario_id' => auth()->id(),
                    'tipo'       => 'ingreso',
                    'concepto'   => 'Venta directa ' . $fileName,
                    'monto'      => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'mensaje' => $fileName . ' emitida correctamente',
                'pdf'     => '/notaria/comprobantes/' . $comprobanteId . '/recibo-ticket',
                'data'    => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('Error venta directa notaría: ' . $e->getMessage());
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }


    public function reenviar($id)
    {
        $empresa = auth()->user()->empresa;
        $comp = \DB::table('comprobantes_sunat')->where('id', $id)->where('empresa_id', $empresa->id)->first();
        
        if (!$comp) {
            return response()->json(['success' => false, 'mensaje' => 'Comprobante no encontrado'], 404);
        }

        $exonerada = $empresa->zona_exonerada;
        $total     = (float) $comp->total;
        $baseImponible = $exonerada ? $total : round($total / 1.18, 2);
        $igv       = $exonerada ? 0 : round($total - $baseImponible, 2);
        $serie     = $comp->serie;
        $correlativo = $comp->numero;
        $fileName  = $empresa->ruc . '-' . $comp->tipo_comprobante . '-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

        // Item genérico con datos del comprobante
        $valUnit = $exonerada ? $total : round($total / 1.18, 4);
        $igvItem = $exonerada ? 0 : round($total - $valUnit, 2);

        $lineas = [[
            'cbc:ID'                  => ['_text' => '1'],
            'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => '1'],
            'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
            'cac:PricingReference' => [
                'cac:AlternativeConditionPrice' => [
                    'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                    'cbc:PriceTypeCode' => ['_text' => '01'],
                ],
            ],
            'cac:TaxTotal' => [
                'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
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
            'cac:Item' => [
                'cbc:Description' => ['_text' => 'Servicio notarial'],
                'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']],
            ],
            'cac:Price' => [
                'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
            ],
        ]];

        $documentBody = [
            'cbc:UBLVersionID'         => ['_text' => '2.1'],
            'cbc:CustomizationID'      => ['_text' => '2.0'],
            'cbc:ID'                   => ['_text' => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT)],
            'cbc:IssueDate'            => ['_text' => $comp->fecha_emision],
            'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $comp->tipo_comprobante],
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
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $comp->cliente_tipo_documento], '_text' => $comp->cliente_numero_documento]],
                    'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => $comp->cliente_nombre]],
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
            'cac:InvoiceLine' => array_values($lineas),
        ];

        \Log::info('ApiSunat reenviar PAYLOAD lineas: ' . json_encode($lineas));
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post('https://back.apisunat.com/personas/v1/sendBill', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                    'documentBody' => $documentBody,
                ]);

            $data = $response->json();
            \Log::info('ApiSunat reenviar response: ' . json_encode($data));

            $aceptada  = $response->successful() && isset($data['sunatResponse']);
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';

            $estado     = $aceptada ? 'aceptado' : ($pendiente ? 'aceptado' : 'rechazado');
            $descripcion = $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data));
            $enlaceXml  = $pendiente && isset($data['xml']) ? $data['xml'] : null;

            \DB::table('comprobantes_sunat')->where('id', $id)->update([
                'aceptada_por_sunat' => $aceptada ? 1 : 0,
                'sunat_descripcion'  => $descripcion,
                'estado'             => $estado,
                'enlace_xml'         => $enlaceXml,
                'updated_at'         => now(),
            ]);

            return response()->json([
                'success' => $aceptada || $pendiente,
                'mensaje' => $aceptada ? $fileName . ' reenviada y aceptada' : 'Rechazada: ' . json_encode($data),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reenviar comprobante: ' . $e->getMessage());
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }


    public function reciboTicket($id)
    {
        $empresa = auth()->user()->empresa;
        $comp = \DB::table('comprobantes_sunat')->where('id', $id)->where('empresa_id', $empresa->id)->first();

        if (!$comp) return abort(404);

        $exonerada = $empresa->zona_exonerada;
        $total     = (float) $comp->total;
        $subtotal  = $exonerada ? $total : (float) $comp->total_gravada;
        $igv       = $exonerada ? 0 : (float) $comp->total_igv;

        // Desglosar: servicio notarial + huella digital (1.50 solo si total >= 10)
        $huella = $total >= 10 ? 1.50 : 0;
        $montoServicio = round($total - $huella, 2);

        // Obtener asunto del acto si existe
        $asunto = 'Servicio notarial';
        if ($comp->acto_id) {
            $acto = \DB::table('actos_notariales')->where('id', $comp->acto_id)->first();
            if ($acto) $asunto = $acto->asunto;
        } elseif ($comp->enlace_cdr) {
            $asunto = $comp->enlace_cdr;
        }

        $items = array_filter([
            [
                'descripcion'    => $asunto,
                'cantidad'       => 1,
                'precio_unitario'=> $montoServicio,
                'total'          => $montoServicio,
            ],
            ...($huella > 0 ? [[
                'descripcion'    => 'Uso biométrico',
                'cantidad'       => 1,
                'precio_unitario'=> $huella,
                'total'          => $huella,
            ]] : []),
        ], fn($item) => $item['precio_unitario'] > 0);
        if (empty($items)) {
            $items = [[
                'descripcion'    => $asunto,
                'cantidad'       => 1,
                'precio_unitario'=> $total,
                'total'          => $total,
            ]];
        }

        $tipoDoc = $comp->tipo_comprobante === '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA ELECTRÓNICA';
        $serie   = $comp->serie;
        $numero  = str_pad($comp->numero, 8, '0', STR_PAD_LEFT);
        $fecha   = \Carbon\Carbon::parse($comp->fecha_emision)->format('d/m/Y');

        $totalLetras = $this->numeroALetras($total);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.recibo-notaria-ticket', [
            'empresa'           => $empresa,
            'tipoDoc'           => $tipoDoc,
            'serie'             => $serie,
            'numero'            => $numero,
            'fecha'             => $fecha,
            'clienteNombre'     => $comp->cliente_nombre,
            'clienteDocumento'  => $comp->cliente_numero_documento,
            'clienteDireccion'  => '',
            'items'             => $items,
            'subtotal'          => $subtotal,
            'igv'               => $igv,
            'total'             => $total,
            'totalLetras'       => $totalLetras,
            'vendedor'          => auth()->user()->name ?? 'ADMIN',
            'metodoPago'        => 'EFECTIVO',
            'exonerada'         => $exonerada,
            'tipoComp'          => $comp->tipo_comprobante,
            'comp'              => $comp,
        ])->setPaper([0, 0, 226.77, 700], 'portrait');

        return $pdf->stream($serie . '-' . $numero . '.pdf');
    }

    private function numeroALetras($numero)
    {
        $entero  = (int)$numero;
        $decimal = round(($numero - $entero) * 100);
        return strtoupper($this->enLetras($entero)) . ' CON ' . str_pad($decimal, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
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


    public function anular(Request $request, $id)
    {
        $empresa = auth()->user()->empresa;
        $comp = \DB::table('comprobantes_sunat')->where('id', $id)->where('empresa_id', $empresa->id)->first();

        if (!$comp) {
            return response()->json(['success' => false, 'mensaje' => 'Comprobante no encontrado'], 404);
        }

        if ($comp->estado === 'anulado') {
            return response()->json(['success' => false, 'mensaje' => 'El comprobante ya está anulado'], 400);
        }

        $motivo = $request->input('motivo', 'Error en emisión');
        $fechaBaja = now()->format('Y-m-d');
        $fileName  = $empresa->ruc . '-' . $comp->tipo_comprobante . '-' . $comp->serie . '-' . str_pad($comp->numero, 8, '0', STR_PAD_LEFT);

        try {
            $response = \Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('https://back.apisunat.com/personas/v1/sendSummary', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $empresa->ruc . '-RA-' . now()->format('Ymd') . '-1',
                    'content' => [
                        'fechaEmision'  => $fechaBaja,
                        'tipoDocumento' => '07',
                        'correlativo'   => '1',
                        'detalles'      => [[
                            'tipoDocumento'         => $comp->tipo_comprobante,
                            'serie'                 => $comp->serie,
                            'correlativoInicio'     => $comp->numero,
                            'correlativoFin'        => $comp->numero,
                            'motivoBaja'            => $motivo,
                        ]],
                    ],
                ]);

            $result = $response->json();

            \DB::table('comprobantes_sunat')->where('id', $id)->update([
                'estado'            => 'anulado',
                'sunat_descripcion' => json_encode($result),
                'updated_at'        => now(),
            ]);

            return response()->json(['success' => true, 'mensaje' => 'Comprobante anulado correctamente', 'resultado' => $result]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'mensaje' => 'Error al anular: ' . $e->getMessage()], 500);
        }
    }

}
