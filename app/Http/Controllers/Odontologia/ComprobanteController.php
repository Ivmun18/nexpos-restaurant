<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoComprobante;
use App\Models\Odontologia\OdontoPago;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ComprobanteController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $comprobantes = \DB::table('odonto_comprobantes as c')
            ->join('odonto_pacientes as p', 'c.paciente_id', '=', 'p.id')
            ->where('c.empresa_id', $empresaId)
            ->whereBetween('c.fecha_emision', [$desde, $hasta])
            ->orderByDesc('c.numero')
            ->orderBy('c.tipo_comprobante')
            ->select('c.*', \DB::raw("CONCAT(p.apellidos, ', ', p.nombres) as paciente_nombre"))
            ->get();

        $totalVentas = $comprobantes->whereIn('estado', ['aceptado','emitido'])->sum('total');

        return Inertia::render('Odontologia/Facturacion/Index', compact('comprobantes','totalVentas','desde','hasta'));
    }

    public function emitir(Request $request) {
        $request->validate([
            'paciente_id'      => 'required|exists:odonto_pacientes,id',
            'pago_id'          => 'nullable|exists:odonto_pagos,id',
            'tipo_comprobante' => 'required|in:01,03',
            'total'            => 'required|numeric|min:0.01',
            'cliente_nombre'   => 'required|string',
            'cliente_documento'=> 'nullable|string',
            'descripcion'      => 'required|string',
        ]);

        $empresa = auth()->user()->empresa;
        $empresaId = $empresa->id;

        // Obtener correlativo
        if ($request->tipo_comprobante === '01') {
            $serie = $empresa->serie_factura ?? 'F001';
            $numero = ($empresa->ultimo_num_factura ?? 0) + 1;
            $empresa->increment('ultimo_num_factura');
        } else {
            $serie = $empresa->serie_boleta ?? 'B001';
            $numero = ($empresa->ultimo_num_boleta ?? 0) + 1;
            $empresa->increment('ultimo_num_boleta');
        }

        $fileName = $empresa->ruc . '-' . $request->tipo_comprobante . '-' . $serie . '-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
        $total = round(floatval($request->total), 2);
        $exonerada = $empresa->zona_exonerada;
        $gravada = $exonerada ? 0 : round($total / 1.18, 2);
        $igv = $exonerada ? 0 : round($total - $gravada, 2);

        // Construir payload ApiSunat
        $payload = [
            'personaId'    => $empresa->apisunat_ruc,
            'personaToken' => $empresa->apisunat_token,
            'fileName'     => $fileName,
            'content' => [
                'cbc:UBLVersionID'         => ['_text' => '2.1'],
                'cbc:CustomizationID'      => ['_text' => '2.0'],
                'cbc:ID'                   => ['_text' => $serie . '-' . str_pad($numero, 8, '0', STR_PAD_LEFT)],
                'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
                'cbc:IssueTime'            => ['_text' => now()->format('H:i:s')],
                'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $request->tipo_comprobante],
                'cbc:Note'                 => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => $this->numeroALetras($total)],
                'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
                'cac:PaymentTerms'         => ['cbc:ID' => ['_text' => 'FormaPago'], 'cbc:PaymentMeansID' => ['_text' => 'Contado']],
                'cac:AccountingSupplierParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                    'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                    'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => $empresa->razon_social]],
                ]],
                'cac:AccountingCustomerParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $request->tipo_comprobante === '01' ? '6' : '1'], '_text' => $request->cliente_documento ?? '00000000']],
                    'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($request->cliente_nombre)]],
                ]],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxSubtotal' => [[
                        'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $exonerada ? $total : $gravada],
                        'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                        'cac:TaxCategory'   => [
                            'cbc:Percent'                => ['_text' => $exonerada ? '0' : '18'],
                            'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                            'cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']],
                        ],
                    ]],
                ],
                'cac:LegalMonetaryTotal' => [
                    'cbc:LineExtensionAmount'  => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $exonerada ? $total : $gravada],
                    'cbc:TaxInclusiveAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                    'cbc:PayableAmount'        => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $total],
                ],
                'cac:InvoiceLine' => [[
                    'cbc:ID'                  => ['_text' => '1'],
                    'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => '1'],
                    'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $exonerada ? $total : $gravada],
                    'cac:TaxTotal' => [
                        'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                        'cac:TaxSubtotal' => [[
                            'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $exonerada ? $total : $gravada],
                            'cbc:TaxAmount'     => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                            'cac:TaxCategory'   => [
                                'cbc:Percent'                => ['_text' => $exonerada ? '0' : '18'],
                                'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                                'cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']],
                            ],
                        ]],
                    ],
                    'cac:Item' => [
                        'cbc:Description' => ['_text' => $request->descripcion],
                        'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']],
                    ],
                    'cac:Price' => ['cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $exonerada ? $total : $gravada]],
                ]],
            ],
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post('https://back.apisunat.com/personas/v1/sendBill', $payload);

            $data = $response->json();
            $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';
            $aceptada  = $response->successful() && isset($data['sunatResponse']);

            $comp = \DB::table('odonto_comprobantes')->insertGetId([
                'empresa_id'        => $empresaId,
                'pago_id'           => $request->pago_id,
                'paciente_id'       => $request->paciente_id,
                'tipo_comprobante'  => $request->tipo_comprobante,
                'serie'             => $serie,
                'numero'            => $numero,
                'fecha_emision'     => now()->toDateString(),
                'total'             => $total,
                'cliente_nombre'    => strtoupper($request->cliente_nombre),
                'cliente_documento' => $request->cliente_documento,
                'estado'            => $aceptada || $pendiente ? 'aceptado' : 'rechazado',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => $fileName . ' emitida correctamente',
                'estado'  => $pendiente ? 'PENDIENTE' : ($aceptada ? 'ACEPTADO' : 'RECHAZADO'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
        }
    }

    private function numeroALetras($numero) {
        $entero  = (int)$numero;
        $decimal = round(($numero - $entero) * 100);
        return strtoupper($this->enLetras($entero)) . ' CON ' . str_pad($decimal, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
    }

    private function enLetras($n) {
        $u = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE','DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE'];
        $d = ['','','VEINTE','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
        $c = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS','SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];
        if ($n == 0) return 'CERO';
        if ($n == 100) return 'CIEN';
        if ($n < 16) return $u[$n];
        if ($n < 20) return 'DIECI' . $u[$n - 10];
        if ($n == 20) return 'VEINTE';
        if ($n < 30) return 'VEINTI' . $u[$n - 20];
        if ($n < 100) return $d[intdiv($n,10)] . ($n % 10 ? ' Y ' . $u[$n % 10] : '');
        if ($n < 1000) return $c[intdiv($n,100)] . ($n % 100 ? ' ' . $this->enLetras($n % 100) : '');
        if ($n < 2000) return 'MIL' . ($n % 1000 ? ' ' . $this->enLetras($n % 1000) : '');
        if ($n < 1000000) return $this->enLetras(intdiv($n,1000)) . ' MIL' . ($n % 1000 ? ' ' . $this->enLetras($n % 1000) : '');
        return $n;
    }
}
