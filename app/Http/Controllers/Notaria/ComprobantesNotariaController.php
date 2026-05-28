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
            'tipo_comprobante'        => 'required|in:01,03',
            'cliente_tipo_documento'  => 'required|in:1,6',
            'cliente_numero_documento'=> 'required|string',
            'cliente_nombre'          => 'required|string',
            'cliente_email'           => 'nullable|email',
        ]);

        $empresa = Empresa::find($acto->empresa_id);

        // Calcular montos
        $total       = $acto->monto_cobrar;
        $gravada     = round($total / 1.18, 2);
        $igv         = round($total - $gravada, 2);

        // Serie según tipo
        $serie = $request->tipo_comprobante === '01' ? 'F001' : 'B001';

        // Obtener siguiente correlativo
        $ultimo = \DB::table('comprobantes_sunat')
            ->where('empresa_id', $empresa->id)
            ->where('serie', $serie)
            ->max('numero') ?? 0;
        $correlativo = $ultimo + 1;

        // Item del servicio notarial
        $items = [[
            'unidad_de_medida'           => 'ZZ',
            'descripcion'                => $acto->tipo_acto_label ?? $acto->asunto,
            'cantidad'                   => 1,
            'valor_unitario'             => $gravada,
            'porcentaje_igv'             => 18,
            'codigo_tipo_afectacion_igv' => '10',
            'nombre_tributo'             => 'IGV',
        ]];

        $payload = [
            'documento'                   => $request->tipo_comprobante === '01' ? 'factura' : 'boleta',
            'serie'                       => $serie,
            'numero'                      => $correlativo,
            'fecha_de_emision'            => now()->format('Y-m-d'),
            'moneda'                      => 'PEN',
            'tipo_operacion'              => '0101',
            'cliente_tipo_de_documento'   => $request->cliente_tipo_documento,
            'cliente_numero_de_documento' => $request->cliente_numero_documento,
            'cliente_denominacion'        => strtoupper($request->cliente_nombre),
            'cliente_direccion'           => $request->cliente_direccion ?? '',
            'cliente_correo'              => $request->cliente_email ?? '',
            'total_gravada'               => $gravada,
            'total_exonerada'             => 0,
            'total_inafecta'              => 0,
            'total_igv'                   => $igv,
            'total'                       => $total,
            'items'                       => $items,
        ];

        try {
            // Usar Nubefact
            $url = 'https://api.nubefact.com/api/v1/';

            $nubefactPayload = [
                'operacion'              => 'generar_comprobante',
                'tipo_de_comprobante'    => $request->tipo_comprobante === '01' ? 1 : 2,
                'serie'                  => $serie,
                'numero'                 => $correlativo,
                'sunat_transaction'      => 1,
                'cliente_tipo_de_documento' => $request->tipo_comprobante === '01' ? 6 : 1,
                'cliente_numero_de_documento' => $request->cliente_numero_documento,
                'cliente_denominacion'   => strtoupper($request->cliente_nombre),
                'cliente_direccion'      => $request->cliente_direccion ?? '',
                'cliente_email'          => $request->cliente_email ?? '',
                'cliente_email_1'        => '',
                'fecha_de_emision'       => now()->format('d-m-Y'),
                'fecha_de_vencimiento'   => '',
                'moneda'                 => 1,
                'tipo_de_cambio'         => '',
                'porcentaje_de_igv'      => 18,
                'total_gravada'          => $gravada,
                'total_exonerada'        => '',
                'total_inafecta'         => '',
                'total_igv'              => $igv,
                'total'                  => $total,
                'detraccion'             => false,
                'observaciones'          => $acto->asunto,
                'items'                  => [[
                    'unidad_de_medida'   => 'ZZ',
                    'codigo'             => 'S/C',
                    'descripcion'        => $acto->asunto,
                    'cantidad'           => 1,
                    'valor_unitario'     => $gravada,
                    'precio_unitario'    => $total,
                    'descuento'          => '',
                    'subtotal'           => $gravada,
                    'tipo_de_igv'        => 1,
                    'igv'                => $igv,
                    'total'              => $total,
                    'anticipo_regularizacion' => false,
                ]],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Token token=' . $empresa->nubefact_token,
                'Content-Type'  => 'application/json',
            ])->post($url, $nubefactPayload);

            $data     = $response->json();
            \Illuminate\Support\Facades\Log::info('Nubefact response: ' . json_encode($data));
            $aceptada = $response->successful() && isset($data['enlace_del_pdf']);

            // Guardar comprobante
            $comprobante = \DB::table('comprobantes_sunat')->insertGetId([
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
                'sunat_descripcion'        => $aceptada ? 'Aceptada' : json_encode($data),
                'enlace_pdf'               => $data['enlace_del_pdf'] ?? null,
                'enlace_xml'               => $data['enlace_del_xml'] ?? null,
                'estado'                   => $aceptada ? 'aceptado' : 'rechazado',
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            // Marcar acto como facturado
            $acto->update(['estado_pago' => 'pagado']);

            if ($aceptada) {
                return response()->json([
                    'success'   => true,
                    'mensaje'   => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT) . ' emitida correctamente',
                    'pdf'       => $data['enlace_del_pdf'] ?? null,
                    'xml'       => $data['enlace_del_xml'] ?? null,
                ]);
            }

            $mensaje = $data['errors'] ?? $data['error'] ?? $data['mensaje'] ?? 'Token de facturación no configurado. Configure su token Nubefact en Ajustes → Configuración → Facturación.';
            return response()->json([
                'success' => false,
                'mensaje' => is_array($mensaje) ? implode(', ', $mensaje) : $mensaje,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error facturación notaría: ' . $e->getMessage());
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

        $fileName = $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

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
            'cbc:ID'                   => ['_text' => $fileName],
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
                ->timeout(30)
                ->post('https://back.apisunat.com/personas/v1/sendBill', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                    'documentBody' => $documentBody,
                ]);

            $data = $response->json();
            \Log::info('ApiSunat ventaDirecta response: ' . json_encode($data));

            $aceptada = $response->successful() && isset($data['sunatResponse']);
            $pdfUrl   = null;

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
                'sunat_descripcion'        => $aceptada ? 'Aceptada' : json_encode($data),
                'enlace_pdf'               => $pdfUrl,
                'estado'                   => $aceptada ? 'aceptado' : 'emitido',
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

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
                'pdf'     => $pdfUrl,
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
        $fileName  = $comp->serie . '-' . str_pad($comp->numero, 8, '0', STR_PAD_LEFT);

        // Item genérico con datos del comprobante
        $valUnit = $exonerada ? $total : round($total / 1.18, 4);
        $igvItem = $exonerada ? 0 : round($total - $valUnit, 2);

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
            'cbc:ID'                   => ['_text' => $fileName],
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
            'cac:InvoiceLine' => $lineas,
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post('https://back.apisunat.com/personas/v1/sendBill', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                    'documentBody' => $documentBody,
                ]);

            $data = $response->json();
            \Log::info('ApiSunat reenviar response: ' . json_encode($data));

            $aceptada = $response->successful() && isset($data['sunatResponse']);

            \DB::table('comprobantes_sunat')->where('id', $id)->update([
                'aceptada_por_sunat' => $aceptada ? 1 : 0,
                'sunat_descripcion'  => $aceptada ? 'Aceptada' : json_encode($data),
                'estado'             => $aceptada ? 'aceptado' : 'rechazado',
                'updated_at'         => now(),
            ]);

            return response()->json([
                'success' => $aceptada,
                'mensaje' => $aceptada ? $fileName . ' reenviada y aceptada' : 'Rechazada: ' . json_encode($data),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reenviar comprobante: ' . $e->getMessage());
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }

}
