<?php

namespace App\Http\Controllers;

use App\Models\ComprobanteSunat;
use App\Models\CajaRestaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ComprobanteSunatController extends Controller
{
    /**
     * Lista de comprobantes emitidos
     */
    public function index(Request $request): Response
    {
        $empresaId = auth()->user()->empresa_id;

        $comprobantes = ComprobanteSunat::where('empresa_id', $empresaId)
            ->with(['caja.mesa', 'caja.user'])
            ->when($request->desde, fn($q, $d) => $q->whereDate('fecha_emision', '>=', $d))
            ->when($request->hasta, fn($q, $h) => $q->whereDate('fecha_emision', '<=', $h))
            ->when($request->tipo && $request->tipo !== 'ticket', fn($q, $t) => $q->where('tipo_comprobante', $t))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($c) => [
                'id'          => 'sunat-' . $c->id,
                'tipo'        => match ($c->tipo_comprobante) {
                    '01'    => 'Factura',
                    '03'    => 'Boleta',
                    '07'    => 'Nota Crédito',
                    default => 'Comprobante',
                },
                'numero'      => $c->numero_completo,
                'fecha'       => $c->fecha_emision,
                'created_at'  => $c->created_at,
                'cliente'     => $c->cliente_nombre,
                'cajero'      => $c->caja?->user?->name ?? '—',
                'total'       => $c->total,
                'estado'      => $c->estado,
                'mesa'        => $c->caja?->mesa?->nombre,
                'metodo_pago' => $c->caja?->metodo_pago,
                'source'      => 'sunat',
                'source_id'   => $c->id,
            ]);

        $tickets = \App\Models\CajaRestaurante::where('empresa_id', $empresaId)
            ->where('tipo_comprobante', 'ninguno')
            ->whereDoesntHave('comprobante')
            ->with(['mesa', 'user'])
            ->when($request->desde, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->hasta, fn($q, $h) => $q->whereDate('created_at', '<=', $h))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'          => 'ticket-' . $t->id,
                'tipo'        => 'Ticket',
                'numero'      => '—',
                'fecha'       => $t->created_at,
                'created_at'  => $t->created_at,
                'cliente'     => $t->user?->name ?? '—',
                'cajero'      => $t->user?->name ?? '—',
                'total'       => $t->total,
                'estado'      => 'ticket',
                'mesa'        => $t->mesa?->nombre,
                'metodo_pago' => $t->metodo_pago,
                'source'      => 'ticket',
                'source_id'   => $t->id,
            ]);

        // Solo incluir tickets si el filtro tipo es 'ticket' o no hay filtro
        $todos = match (true) {
            $request->tipo === 'ticket' => $tickets,
            !$request->tipo             => $comprobantes->concat($tickets),
            default                     => $comprobantes,
        };

        $todos = $todos->sortByDesc('created_at')->values();

        // Paginar manualmente (colección combinada de dos tablas distintas)
        $page      = (int) $request->input('page', 1);
        $perPage   = 20;
        $resultado = new \Illuminate\Pagination\LengthAwarePaginator(
            $todos->slice(($page - 1) * $perPage, $perPage)->values(),
            $todos->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return Inertia::render('Comprobantes/Index', [
            'comprobantes' => $resultado,
            'filtros' => [
                'tipo'  => $request->tipo,
                'desde' => $request->desde,
                'hasta' => $request->hasta,
            ],
        ]);
    }

    /**
     * Formulario para emitir comprobante desde caja
     */
    public function crear(CajaRestaurante $caja)
    {
        $comprobanteExistente = ComprobanteSunat::where('caja_restaurante_id', $caja->id)->first();

        if ($comprobanteExistente) {
            return redirect()->route('comprobantes.show', $comprobanteExistente)
                ->with('info', 'Esta venta ya tiene un comprobante emitido');
        }

        return Inertia::render('Comprobantes/Crear', [
            'caja' => $caja->load('mesa'),
        ]);
    }

    /**
     * Emitir boleta de venta
     */
    public function emitirBoleta(Request $request, CajaRestaurante $caja)
    {
        $request->validate([
            'cliente_tipo_documento' => 'required|in:0,1,6',
            'cliente_documento'      => 'required|string|max:11',
            'cliente_nombre'         => 'required|string|max:255',
            'cliente_email'          => 'nullable|email',
        ]);

        $empresa   = auth()->user()->empresa;
        $exonerada = $empresa->zona_exonerada ?? false;
        $total     = round(floatval($caja->total), 2);

        if ($exonerada) {
            $gravada = 0;
            $igv     = 0;
        } else {
            $gravada = round($total / 1.18, 2);
            $igv     = round($total - $gravada, 2);
        }
        $baseImponible = $exonerada ? $total : $gravada;

        // Sin token ApiSunat → guardar local
        if (!$empresa->apisunat_token) {
            $serie  = $empresa->serie_boleta ?? 'B001';
            $numero = ComprobanteSunat::where('empresa_id', $empresa->id)
                          ->where('serie', $serie)->max('numero') + 1;

            $comprobante = ComprobanteSunat::create([
                'empresa_id'               => $empresa->id,
                'caja_restaurante_id'      => $caja->id,
                'tipo_comprobante'         => '03',
                'serie'                    => $serie,
                'numero'                   => $numero,
                'fecha_emision'            => now(),
                'cliente_tipo_documento'   => $request->cliente_tipo_documento,
                'cliente_numero_documento' => $request->cliente_documento,
                'cliente_nombre'           => $request->cliente_nombre,
                'cliente_email'            => $request->cliente_email ?? '',
                'total_gravada'            => $gravada,
                'total_igv'                => $igv,
                'total'                    => $total,
                'estado'                   => 'emitido',
            ]);

            return redirect()->route('comprobantes.show', $comprobante)
                ->with('success', 'Boleta registrada localmente.')
                ->with('imprimir', true);
        }

        // Correlativo
        $serie       = $empresa->serie_boleta ?? 'B001';
        $correlativo = ($empresa->ultimo_num_boleta ?? 0) + 1;
        $empresa->increment('ultimo_num_boleta');

        $fileName = $empresa->ruc . '-03-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

        // Descripción del consumo
        $mesaNumero  = $caja->mesa->numero ?? 'S/N';
        $descripcion = 'Consumo en Mesa ' . $mesaNumero;

        $valUnit = $exonerada ? $total : round($total / 1.18, 4);
        $igvItem = $exonerada ? 0 : round($total - $valUnit, 2);

        $lineas = [[
            'cbc:ID'                  => ['_text' => '1'],
            'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'NIU'], '_text' => '1'],
            'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
            'cac:PricingReference'    => [
                'cac:AlternativeConditionPrice' => [
                    'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                    'cbc:PriceTypeCode' => ['_text' => '01'],
                ],
            ],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                    'cac:TaxCategory'   => [
                        'cbc:Percent'                => ['_text' => $exonerada ? '0' : '18'],
                        'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                        'cac:TaxScheme' => [
                            'cbc:ID'          => ['_text' => $exonerada ? '9997' : '1000'],
                            'cbc:Name'        => ['_text' => $exonerada ? 'EXO' : 'IGV'],
                            'cbc:TaxTypeCode' => ['_text' => 'VAT'],
                        ],
                    ],
                ]],
            ],
            'cac:Item' => [
                'cbc:Description'              => ['_text' => $descripcion],
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
            'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
            'cbc:IssueTime'            => ['_text' => now()->format('H:i:s')],
            'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => '03'],
            'cbc:Note'                 => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => $this->numeroALetras($total)],
            'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
            'cac:PaymentTerms'         => [
                'cbc:ID'             => ['_text' => 'FormaPago'],
                'cbc:PaymentMeansID' => ['_text' => 'Contado'],
            ],
            'cac:AccountingSupplierParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                'cac:PartyLegalEntity'    => [
                    'cbc:RegistrationName'    => ['_text' => $empresa->razon_social],
                    'cac:RegistrationAddress' => [
                        'cbc:AddressTypeCode' => ['_text' => '0000'],
                        'cac:AddressLine'     => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']],
                    ],
                ],
            ]],
            'cac:AccountingCustomerParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $request->cliente_tipo_documento], '_text' => $request->cliente_documento]],
                'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($request->cliente_nombre)]],
            ]],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxCategory'   => [
                        'cac:TaxScheme' => [
                            'cbc:ID'          => ['_text' => $exonerada ? '9997' : '1000'],
                            'cbc:Name'        => ['_text' => $exonerada ? 'EXO' : 'IGV'],
                            'cbc:TaxTypeCode' => ['_text' => 'VAT'],
                        ],
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

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post('https://back.apisunat.com/personas/v1/sendBill', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                    'documentBody' => $documentBody,
                ]);

            $data      = $response->json();
            Log::info('ApiSunat emitirBoleta response: ' . json_encode($data));

            $aceptada  = $response->successful() && isset($data['sunatResponse']);
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';
            $pdfUrl    = $data['sunatResponse']['enlace_del_pdf'] ?? null;

            $comprobante = ComprobanteSunat::create([
                'empresa_id'               => $empresa->id,
                'caja_restaurante_id'      => $caja->id,
                'tipo_comprobante'         => '03',
                'serie'                    => $serie,
                'numero'                   => $correlativo,
                'fecha_emision'            => now()->toDateString(),
                'cliente_tipo_documento'   => $request->cliente_tipo_documento,
                'cliente_numero_documento' => $request->cliente_documento,
                'cliente_nombre'           => strtoupper($request->cliente_nombre),
                'cliente_email'            => $request->cliente_email ?? '',
                'total_gravada'            => $gravada,
                'total_igv'                => $igv,
                'total'                    => $total,
                'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                'sunat_descripcion'        => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                'enlace_xml'               => $pendiente && isset($data['xml']) ? $data['xml'] : null,
                'enlace_pdf'               => $pdfUrl,
                'apisunat_document_id'     => substr($data['documentId'] ?? '', 0, 100) ?: null,
                'estado'                   => $aceptada ? 'aceptado' : ($pendiente ? 'pendiente' : 'rechazado'),
            ]);

            return redirect()->route('comprobantes.show', $comprobante)
                ->with('success', $aceptada ? '¡Boleta emitida y aceptada por SUNAT!' : '¡Boleta emitida!');

        } catch (\Exception $e) {
            Log::error('Error emitirBoleta restaurante: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al emitir: ' . $e->getMessage()]);
        }
    }

    /**
     * Emitir factura
     */
    public function emitirFactura(Request $request, CajaRestaurante $caja)
    {
        $request->validate([
            'cliente_ruc'          => 'required|string|size:11',
            'cliente_razon_social' => 'required|string|max:255',
            'cliente_direccion'    => 'required|string|max:255',
            'cliente_email'        => 'nullable|email',
        ]);

        $empresa   = auth()->user()->empresa;
        $exonerada = $empresa->zona_exonerada ?? false;
        $total     = round(floatval($caja->total), 2);

        if ($exonerada) {
            $gravada = 0;
            $igv     = 0;
        } else {
            $gravada = round($total / 1.18, 2);
            $igv     = round($total - $gravada, 2);
        }
        $baseImponible = $exonerada ? $total : $gravada;

        if (!$empresa->apisunat_token) {
            return back()->withErrors(['error' => 'No hay token de facturación configurado.']);
        }

        // Correlativo
        $serie       = $empresa->serie_factura ?? 'F001';
        $correlativo = ($empresa->ultimo_num_factura ?? 0) + 1;
        $empresa->increment('ultimo_num_factura');

        $fileName    = $empresa->ruc . '-01-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);
        $mesaNumero  = $caja->mesa->numero ?? 'S/N';
        $descripcion = 'Consumo en Mesa ' . $mesaNumero;

        $valUnit = $exonerada ? $total : round($total / 1.18, 4);
        $igvItem = $exonerada ? 0 : round($total - $valUnit, 2);

        $lineas = [[
            'cbc:ID'                  => ['_text' => '1'],
            'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'NIU'], '_text' => '1'],
            'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
            'cac:PricingReference'    => [
                'cac:AlternativeConditionPrice' => [
                    'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                    'cbc:PriceTypeCode' => ['_text' => '01'],
                ],
            ],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                    'cac:TaxCategory'   => [
                        'cbc:Percent'                => ['_text' => $exonerada ? '0' : '18'],
                        'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                        'cac:TaxScheme' => [
                            'cbc:ID'          => ['_text' => $exonerada ? '9997' : '1000'],
                            'cbc:Name'        => ['_text' => $exonerada ? 'EXO' : 'IGV'],
                            'cbc:TaxTypeCode' => ['_text' => 'VAT'],
                        ],
                    ],
                ]],
            ],
            'cac:Item' => [
                'cbc:Description'               => ['_text' => $descripcion],
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
            'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
            'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => '01'],
            'cbc:Note'                 => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => $this->numeroALetras($total)],
            'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
            'cac:PaymentTerms'         => [
                'cbc:ID'             => ['_text' => 'FormaPago'],
                'cbc:PaymentMeansID' => ['_text' => 'Contado'],
            ],
            'cac:AccountingSupplierParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                'cac:PartyLegalEntity'    => [
                    'cbc:RegistrationName'    => ['_text' => $empresa->razon_social],
                    'cac:RegistrationAddress' => [
                        'cbc:AddressTypeCode' => ['_text' => '0000'],
                        'cac:AddressLine'     => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']],
                    ],
                ],
            ]],
            'cac:AccountingCustomerParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $request->cliente_ruc]],
                'cac:PartyLegalEntity'    => [
                    'cbc:RegistrationName' => ['_text' => strtoupper($request->cliente_razon_social)],
                    'cac:RegistrationAddress' => [
                        'cbc:AddressTypeCode' => ['_text' => '0000'],
                        'cac:AddressLine'     => ['cbc:Line' => ['_text' => $request->cliente_direccion]],
                    ],
                ],
            ]],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxCategory'   => [
                        'cac:TaxScheme' => [
                            'cbc:ID'          => ['_text' => $exonerada ? '9997' : '1000'],
                            'cbc:Name'        => ['_text' => $exonerada ? 'EXO' : 'IGV'],
                            'cbc:TaxTypeCode' => ['_text' => 'VAT'],
                        ],
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

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post('https://back.apisunat.com/personas/v1/sendBill', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                    'documentBody' => $documentBody,
                ]);

            $data      = $response->json();
            Log::info('ApiSunat emitirFactura response: ' . json_encode($data));

            $aceptada  = $response->successful() && isset($data['sunatResponse']);
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';
            $pdfUrl    = $data['sunatResponse']['enlace_del_pdf'] ?? null;

            $comprobante = ComprobanteSunat::create([
                'empresa_id'               => $empresa->id,
                'caja_restaurante_id'      => $caja->id,
                'tipo_comprobante'         => '01',
                'serie'                    => $serie,
                'numero'                   => $correlativo,
                'fecha_emision'            => now()->toDateString(),
                'cliente_tipo_documento'   => '6',
                'cliente_numero_documento' => $request->cliente_ruc,
                'cliente_nombre'           => strtoupper($request->cliente_razon_social),
                'cliente_direccion'        => $request->cliente_direccion,
                'cliente_email'            => $request->cliente_email ?? '',
                'total_gravada'            => $gravada,
                'total_igv'                => $igv,
                'total'                    => $total,
                'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                'sunat_descripcion'        => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                'enlace_xml'               => $pendiente && isset($data['xml']) ? $data['xml'] : null,
                'enlace_pdf'               => $pdfUrl,
                'apisunat_document_id'     => substr($data['documentId'] ?? '', 0, 100) ?: null,
                'estado'                   => $aceptada ? 'aceptado' : ($pendiente ? 'pendiente' : 'rechazado'),
            ]);

            return redirect()->route('comprobantes.show', $comprobante)
                ->with('success', $aceptada ? '¡Factura emitida y aceptada por SUNAT!' : '¡Factura emitida!');

        } catch (\Exception $e) {
            Log::error('Error emitirFactura restaurante: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al emitir: ' . $e->getMessage()]);
        }
    }

    /**
     * Formulario para emitir nota de crédito sobre un comprobante existente
     */
    public function crearNotaCredito($id)
    {
        $empresa = auth()->user()->empresa;
        $comprobante = ComprobanteSunat::where('id', $id)
            ->where('empresa_id', $empresa->id)
            ->with('caja.mesa')
            ->firstOrFail();

        return Inertia::render('Comprobantes/NotaCreditoCrear', [
            'comprobante' => $comprobante,
        ]);
    }

    /**
     * Emitir nota de crédito referenciando un comprobante existente
     */
    public function emitirNotaCredito(Request $request, $id)
    {
        $request->validate([
            'motivo_codigo'      => 'required|string|max:2',
            'motivo_descripcion' => 'required|string|max:200',
        ]);

        $empresa  = auth()->user()->empresa;
        $original = ComprobanteSunat::where('id', $id)
            ->where('empresa_id', $empresa->id)
            ->first();

        if (!$original) {
            return back()->withErrors(['error' => 'Comprobante original no encontrado']);
        }

        if (!in_array($original->tipo_comprobante, ['01', '03'])) {
            return back()->withErrors(['error' => 'Solo se puede emitir nota de crédito sobre una boleta o factura']);
        }

        $exonerada = $empresa->zona_exonerada ?? false;
        $total     = round(floatval($original->total), 2);
        $gravada   = round(floatval($original->total_gravada), 2);
        $igv       = round(floatval($original->total_igv), 2);

        // Serie y correlativo según tipo del comprobante original
        if ($original->tipo_comprobante === '03') {
            $serie       = $empresa->serie_nota_credito ?? 'BC01';
            $correlativo = ($empresa->ultimo_num_nota_credito ?? 0) + 1;
            $empresa->increment('ultimo_num_nota_credito');
        } else {
            $serie       = $empresa->serie_nota_credito_factura ?? 'FC01';
            $correlativo = ($empresa->ultimo_num_nota_credito_factura ?? 0) + 1;
            $empresa->increment('ultimo_num_nota_credito_factura');
        }

        $fileName = $empresa->ruc . '-07-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

        // Sin token ApiSunat → guardar local
        if (!$empresa->apisunat_token) {
            $comprobante = ComprobanteSunat::create([
                'empresa_id'               => $empresa->id,
                'caja_restaurante_id'      => $original->caja_restaurante_id,
                'tipo_comprobante'         => '07',
                'serie'                    => $serie,
                'numero'                   => $correlativo,
                'fecha_emision'            => now(),
                'cliente_tipo_documento'   => $original->cliente_tipo_documento,
                'cliente_numero_documento' => $original->cliente_numero_documento,
                'cliente_nombre'           => $original->cliente_nombre,
                'cliente_direccion'        => $original->cliente_direccion,
                'cliente_email'            => $original->cliente_email,
                'total_gravada'            => $gravada,
                'total_igv'                => $igv,
                'total'                    => $total,
                'estado'                   => 'emitido',
            ]);

            return redirect()->route('comprobantes.show', $comprobante)
                ->with('success', 'Nota de crédito registrada localmente.')
                ->with('imprimir', true);
        }

        $mesaNumero  = $original->caja->mesa->numero ?? 'S/N';
        $descripcion = 'Consumo en Mesa ' . $mesaNumero;

        $valUnit = $exonerada ? $total : round($total / 1.18, 4);
        $igvItem = $exonerada ? 0 : round($total - $valUnit, 2);

        $taxCategory = [
            'cbc:Percent'                => ['_text' => $exonerada ? '0' : '18'],
            'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
            'cac:TaxScheme' => [
                'cbc:ID'          => ['_text' => $exonerada ? '9997' : '1000'],
                'cbc:Name'        => ['_text' => $exonerada ? 'EXO' : 'IGV'],
                'cbc:TaxTypeCode' => ['_text' => 'VAT'],
            ],
        ];

        $lineas = [[
            'cbc:ID'                  => ['_text' => '1'],
            'cbc:CreditedQuantity'    => ['_attributes' => ['unitCode' => 'NIU'], '_text' => '1'],
            'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                    'cac:TaxCategory'   => $taxCategory,
                ]],
            ],
            'cac:Item' => [
                'cbc:Description'               => ['_text' => $descripcion],
                'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']],
            ],
            'cac:Price' => [
                'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
            ],
        ]];

        $clienteParty = [
            'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $original->cliente_tipo_documento], '_text' => $original->cliente_numero_documento]],
            'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($original->cliente_nombre)]],
        ];
        if ($original->cliente_direccion) {
            $clienteParty['cac:PartyLegalEntity']['cac:RegistrationAddress'] = [
                'cbc:AddressTypeCode' => ['_text' => '0000'],
                'cac:AddressLine'     => ['cbc:Line' => ['_text' => $original->cliente_direccion]],
            ];
        }

        $documentBody = [
            'cbc:UBLVersionID'         => ['_text' => '2.1'],
            'cbc:CustomizationID'      => ['_text' => '2.0'],
            'cbc:ID'                   => ['_text' => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT)],
            'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
            'cbc:IssueTime'            => ['_text' => now()->format('H:i:s')],
            'cbc:Note'                 => [['_attributes' => ['languageLocaleID' => '1000'], '_text' => $this->numeroALetras($total)]],
            'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
            'cac:DiscrepancyResponse' => [
                'cbc:ResponseCode' => ['_text' => $request->motivo_codigo],
                'cbc:Description'  => ['_text' => $request->motivo_descripcion],
            ],
            'cac:BillingReference' => [
                'cac:InvoiceDocumentReference' => [
                    'cbc:ID'               => ['_text' => $original->numero_completo],
                    'cbc:DocumentTypeCode' => ['_text' => $original->tipo_comprobante],
                ],
            ],
            'cac:AccountingSupplierParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                'cac:PartyLegalEntity'    => [
                    'cbc:RegistrationName'    => ['_text' => $empresa->razon_social],
                    'cac:RegistrationAddress' => [
                        'cbc:AddressTypeCode' => ['_text' => '0000'],
                        'cac:AddressLine'     => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']],
                    ],
                ],
            ]],
            'cac:AccountingCustomerParty' => ['cac:Party' => $clienteParty],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $gravada],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxCategory'   => $taxCategory,
                ]],
            ],
            'cac:LegalMonetaryTotal' => [
                'cbc:PayableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
            ],
            'cac:CreditNoteLine' => $lineas,
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

            $data      = $response->json();
            Log::info('ApiSunat emitirNotaCredito response: ' . json_encode($data));

            $aceptada  = $response->successful() && isset($data['sunatResponse']);
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';
            $pdfUrl    = $data['sunatResponse']['enlace_del_pdf'] ?? null;

            $comprobante = ComprobanteSunat::create([
                'empresa_id'               => $empresa->id,
                'caja_restaurante_id'      => $original->caja_restaurante_id,
                'tipo_comprobante'         => '07',
                'serie'                    => $serie,
                'numero'                   => $correlativo,
                'fecha_emision'            => now()->toDateString(),
                'cliente_tipo_documento'   => $original->cliente_tipo_documento,
                'cliente_numero_documento' => $original->cliente_numero_documento,
                'cliente_nombre'           => $original->cliente_nombre,
                'cliente_direccion'        => $original->cliente_direccion,
                'cliente_email'            => $original->cliente_email,
                'total_gravada'            => $gravada,
                'total_igv'                => $igv,
                'total'                    => $total,
                'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                'sunat_descripcion'        => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                'enlace_xml'               => $pendiente && isset($data['xml']) ? $data['xml'] : null,
                'enlace_pdf'               => $pdfUrl,
                'apisunat_document_id'     => substr($data['documentId'] ?? '', 0, 100) ?: null,
                'estado'                   => $aceptada ? 'aceptado' : ($pendiente ? 'pendiente' : 'rechazado'),
            ]);

            return redirect()->route('comprobantes.show', $comprobante)
                ->with('success', $aceptada ? '¡Nota de crédito emitida y aceptada por SUNAT!' : '¡Nota de crédito emitida!');

        } catch (\Exception $e) {
            Log::error('Error emitirNotaCredito restaurante: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al emitir: ' . $e->getMessage()]);
        }
    }

    /**
     * Reenviar comprobante rechazado a SUNAT
     */
    public function reenviar($id)
    {
        $empresa = auth()->user()->empresa;
        $comp    = \DB::table('comprobantes_sunat')
                      ->where('id', $id)
                      ->where('empresa_id', $empresa->id)
                      ->first();

        if (!$comp) {
            return response()->json(['success' => false, 'mensaje' => 'Comprobante no encontrado'], 404);
        }

        $exonerada   = $empresa->zona_exonerada ?? false;
        $total       = (float) $comp->total;
        $baseImponible = $exonerada ? $total : round($total / 1.18, 2);
        $igv         = $exonerada ? 0 : round($total - $baseImponible, 2);
        $serie       = $comp->serie;
        $correlativo = $comp->numero;
        $fileName    = $empresa->ruc . '-' . $comp->tipo_comprobante . '-' . $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

        $valUnit = $exonerada ? $total : round($total / 1.18, 4);
        $igvItem = $exonerada ? 0 : round($total - $valUnit, 2);

        $lineas = [[
            'cbc:ID'                  => ['_text' => '1'],
            'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'NIU'], '_text' => '1'],
            'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
            'cac:PricingReference'    => [
                'cac:AlternativeConditionPrice' => [
                    'cbc:PriceAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                    'cbc:PriceTypeCode' => ['_text' => '01'],
                ],
            ],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                    'cac:TaxCategory'   => [
                        'cbc:Percent'                => ['_text' => $exonerada ? '0' : '18'],
                        'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                        'cac:TaxScheme' => [
                            'cbc:ID'          => ['_text' => $exonerada ? '9997' : '1000'],
                            'cbc:Name'        => ['_text' => $exonerada ? 'EXO' : 'IGV'],
                            'cbc:TaxTypeCode' => ['_text' => 'VAT'],
                        ],
                    ],
                ]],
            ],
            'cac:Item' => [
                'cbc:Description'               => ['_text' => 'Consumo restaurante'],
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
            'cbc:Note'                 => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => $this->numeroALetras($total)],
            'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
            'cac:PaymentTerms'         => [
                'cbc:ID'             => ['_text' => 'FormaPago'],
                'cbc:PaymentMeansID' => ['_text' => 'Contado'],
            ],
            'cac:AccountingSupplierParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                'cac:PartyLegalEntity'    => [
                    'cbc:RegistrationName'    => ['_text' => $empresa->razon_social],
                    'cac:RegistrationAddress' => [
                        'cbc:AddressTypeCode' => ['_text' => '0000'],
                        'cac:AddressLine'     => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']],
                    ],
                ],
            ]],
            'cac:AccountingCustomerParty' => ['cac:Party' => [
                'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $comp->cliente_tipo_documento], '_text' => $comp->cliente_numero_documento]],
                'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => $comp->cliente_nombre]],
            ]],
            'cac:TaxTotal' => [
                'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                'cac:TaxSubtotal' => [[
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                    'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxCategory'   => [
                        'cac:TaxScheme' => [
                            'cbc:ID'          => ['_text' => $exonerada ? '9997' : '1000'],
                            'cbc:Name'        => ['_text' => $exonerada ? 'EXO' : 'IGV'],
                            'cbc:TaxTypeCode' => ['_text' => 'VAT'],
                        ],
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

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post('https://back.apisunat.com/personas/v1/sendBill', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                    'documentBody' => $documentBody,
                ]);

            $data      = $response->json();
            Log::info('ApiSunat reenviar response: ' . json_encode($data));

            $aceptada  = $response->successful() && isset($data['sunatResponse']);
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';

            $updatePayload = [
                'aceptada_por_sunat' => $aceptada ? 1 : 0,
                'sunat_descripcion'  => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                'estado'             => $aceptada ? 'aceptado' : ($pendiente ? 'pendiente' : 'rechazado'),
                'updated_at'         => now(),
            ];
            if (!empty($data['documentId'])) {
                $updatePayload['apisunat_document_id'] = substr($data['documentId'], 0, 100);
            }

            \DB::table('comprobantes_sunat')->where('id', $id)->update($updatePayload);

            return response()->json([
                'success' => $aceptada || $pendiente,
                'mensaje' => $aceptada ? $fileName . ' reenviada y aceptada' : 'Rechazada: ' . json_encode($data),
            ]);

        } catch (\Exception $e) {
            Log::error('Error reenviar comprobante restaurante: ' . $e->getMessage());
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }

    /**
     * Anular comprobante
     */
    public function anular(Request $request, $id)
    {
        $empresa = auth()->user()->empresa;
        $comp    = \DB::table('comprobantes_sunat')
                      ->where('id', $id)
                      ->where('empresa_id', $empresa->id)
                      ->first();

        if (!$comp) {
            return response()->json(['success' => false, 'mensaje' => 'Comprobante no encontrado'], 404);
        }

        if ($comp->estado === 'anulado') {
            return response()->json(['success' => false, 'mensaje' => 'El comprobante ya está anulado'], 400);
        }

        $motivo    = $request->input('motivo', 'Error en emisión');
        $fechaBaja = now()->format('Y-m-d');
        $fileName  = $empresa->ruc . '-' . $comp->tipo_comprobante . '-' . $comp->serie . '-' . str_pad($comp->numero, 8, '0', STR_PAD_LEFT);

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post('https://back.apisunat.com/personas/v1/sendSummary', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $empresa->ruc . '-RA-' . now()->format('Ymd') . '-1',
                    'content'      => [
                        'fechaEmision'  => $fechaBaja,
                        'tipoDocumento' => '07',
                        'correlativo'   => '1',
                        'detalles'      => [[
                            'tipoDocumento'     => $comp->tipo_comprobante,
                            'serie'             => $comp->serie,
                            'correlativoInicio' => $comp->numero,
                            'correlativoFin'    => $comp->numero,
                            'motivoBaja'        => $motivo,
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

    /**
     * Consultar estado de un comprobante en ApiSunat/SUNAT
     */
    public function consultarEstado($id)
    {
        $comprobante = ComprobanteSunat::find($id);

        if (!$comprobante) {
            return response()->json(['success' => false, 'mensaje' => 'Comprobante no encontrado'], 404);
        }

        if (auth()->check() && $comprobante->empresa_id !== auth()->user()->empresa_id) {
            return response()->json(['success' => false, 'mensaje' => 'No autorizado'], 403);
        }

        if (!$comprobante->apisunat_document_id) {
            return response()->json(['success' => false, 'mensaje' => 'Comprobante sin document_id de ApiSunat, no se puede consultar'], 422);
        }

        try {
            $response = Http::timeout(30)
                ->get("https://back.apisunat.com/documents/{$comprobante->apisunat_document_id}/getById");

            if (!$response->successful()) {
                Log::warning("consultarEstado comprobante {$comprobante->id} (doc {$comprobante->apisunat_document_id}): HTTP {$response->status()} - " . $response->body());
                return response()->json(['success' => false, 'mensaje' => 'Error HTTP ' . $response->status()], 502);
            }

            $data = $response->json();
            Log::info("consultarEstado comprobante {$comprobante->id} (doc {$comprobante->apisunat_document_id}): " . json_encode($data));

            $status = $data['status'] ?? null;

            if ($status === 'ACEPTADO') {
                $comprobante->update([
                    'estado'             => 'aceptado',
                    'aceptada_por_sunat' => 1,
                    'sunat_descripcion'  => 'Aceptada',
                ]);
            } elseif ($status === 'RECHAZADO') {
                $comprobante->update([
                    'estado'            => 'rechazado',
                    'sunat_descripcion' => json_encode($data),
                ]);
            } elseif ($status === 'EXCEPCION') {
                $comprobante->update([
                    'estado'            => 'rechazado',
                    'sunat_descripcion' => 'Excepción: ' . json_encode($data),
                ]);
            }

            return response()->json([
                'success'         => true,
                'estado'          => $comprobante->fresh()->estado,
                'status_apisunat' => $status,
            ]);

        } catch (\Exception $e) {
            Log::error("Error consultarEstado comprobante {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }

    /**
     * Ver detalle de comprobante
     */
    public function show(ComprobanteSunat $comprobante)
    {
        $comprobante->load(['caja.mesa', 'caja.user', 'empresa']);

        $pedidos = [];
        if ($comprobante->caja_restaurante_id) {
            $pedidos = \App\Models\Pedido::with('detalles.producto')
                ->where('caja_restaurante_id', $comprobante->caja_restaurante_id)
                ->get();
        } elseif ($comprobante->caja && $comprobante->caja->mesa_id) {
            $pedidos = \App\Models\Pedido::with('detalles.producto')
                ->where('mesa_id', $comprobante->caja->mesa_id)
                ->where('estado', 'cerrado')
                ->latest()->take(3)->get();
        }

        return Inertia::render('Comprobantes/Show', [
            'comprobante' => $comprobante,
            'pedidos'     => $pedidos,
            'imprimir'    => session('imprimir', false),
        ]);
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
        if ($n == 0)       return 'CERO';
        if ($n == 100)     return 'CIEN';
        if ($n < 16)       return $u[$n];
        if ($n < 20)       return 'DIECI' . $u[$n - 10];
        if ($n == 20)      return 'VEINTE';
        if ($n < 30)       return 'VEINTI' . $u[$n - 20];
        if ($n < 100)      return $d[intdiv($n,10)] . ($n%10 ? ' Y ' . $u[$n%10] : '');
        if ($n < 1000)     return $c[intdiv($n,100)] . ($n%100 ? ' ' . $this->enLetras($n%100) : '');
        if ($n < 2000)     return 'MIL' . ($n%1000 ? ' ' . $this->enLetras($n%1000) : '');
        if ($n < 1000000)  return $this->enLetras(intdiv($n,1000)) . ' MIL' . ($n%1000 ? ' ' . $this->enLetras($n%1000) : '');
        return (string)$n;
    }
}
