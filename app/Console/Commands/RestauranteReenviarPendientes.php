<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestauranteReenviarPendientes extends Command
{
    protected $signature = 'restaurante:reenviar-pendientes {--empresa=23} {--dry-run}';
    protected $description = 'Reenvía a ApiSunat (sendBill) los comprobantes en estado=pendiente de una empresa, para reconciliar su estado real (mismo patrón probado en Notaria::reenviar)';

    public function handle(): int
    {
        $empresaId = (int) $this->option('empresa');
        $dryRun    = (bool) $this->option('dry-run');

        $empresa = Empresa::find($empresaId);
        if (! $empresa) {
            $this->error("Empresa {$empresaId} no encontrada.");
            return self::FAILURE;
        }
        if (! $empresa->apisunat_ruc || ! $empresa->apisunat_token) {
            $this->error("Empresa {$empresaId} no tiene credenciales ApiSunat configuradas.");
            return self::FAILURE;
        }

        $pendientes = DB::table('comprobantes_sunat')
            ->where('empresa_id', $empresaId)
            ->where('estado', 'pendiente')
            ->orderBy('id')
            ->get();

        $this->info("Reconciliando {$pendientes->count()} comprobante(s) pendiente(s) de empresa {$empresaId}" . ($dryRun ? ' [DRY-RUN]' : ''));

        foreach ($pendientes as $comp) {
            $tipoComp   = $comp->tipo_comprobante;
            $exonerada  = (float) $comp->total_igv === 0.0;
            $totalMonto = (float) $comp->total;
            $baseImponible = $exonerada ? $totalMonto : (float) $comp->total_gravada;
            $igv        = (float) $comp->total_igv;

            $fileName = $empresa->ruc . '-' . $tipoComp . '-' . $comp->serie . '-' . str_pad((string) $comp->numero, 8, '0', STR_PAD_LEFT);

            $valUnit = $exonerada ? $totalMonto : round($totalMonto / 1.18, 4);
            $igvItem = $exonerada ? 0 : round($totalMonto - $valUnit, 2);

            $lineas = [[
                'cbc:ID' => ['_text' => 1],
                'cbc:InvoicedQuantity' => ['_attributes' => ['unitCode' => 'NIU'], '_text' => 1],
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                'cac:PricingReference' => ['cac:AlternativeConditionPrice' => [
                    'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
                    'cbc:PriceTypeCode' => ['_text' => '01'],
                ]],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                    'cac:TaxSubtotal' => [[
                        'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit],
                        'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvItem],
                        'cac:TaxCategory' => [
                            'cbc:Percent' => ['_text' => $exonerada ? '0' : '18'],
                            'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'],
                            'cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']],
                        ],
                    ]],
                ],
                'cac:Item' => ['cbc:Description' => ['_text' => 'Consumo Mesa'], 'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']]],
                'cac:Price' => ['cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valUnit]],
            ]];

            $documentBody = [
                'cbc:UBLVersionID' => ['_text' => '2.1'],
                'cbc:CustomizationID' => ['_text' => '2.0'],
                'cbc:ID' => ['_text' => $comp->serie . '-' . str_pad((string) $comp->numero, 8, '0', STR_PAD_LEFT)],
                'cbc:IssueDate' => ['_text' => $comp->fecha_emision],
                'cbc:InvoiceTypeCode' => ['_attributes' => ['listID' => '0101'], '_text' => $tipoComp],
                'cbc:Note' => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => strtoupper($this->numeroALetras($totalMonto))],
                'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
                'cac:PaymentTerms' => ['cbc:ID' => ['_text' => 'FormaPago'], 'cbc:PaymentMeansID' => ['_text' => 'Contado']],
                'cac:AccountingSupplierParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                    'cac:PartyName' => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                    'cac:PartyLegalEntity' => ['cbc:RegistrationName' => ['_text' => $empresa->razon_social], 'cac:RegistrationAddress' => ['cbc:AddressTypeCode' => ['_text' => '0000'], 'cac:AddressLine' => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']]]],
                ]],
                'cac:AccountingCustomerParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $comp->cliente_tipo_documento], '_text' => $comp->cliente_numero_documento]],
                    'cac:PartyLegalEntity' => ['cbc:RegistrationName' => ['_text' => strtoupper($comp->cliente_nombre)]],
                ]],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxSubtotal' => [[
                        'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                        'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                        'cac:TaxCategory' => ['cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']]],
                    ]],
                ],
                'cac:LegalMonetaryTotal' => [
                    'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                    'cbc:TaxInclusiveAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
                    'cbc:PayableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
                ],
                'cac:InvoiceLine' => $lineas,
            ];

            if ($dryRun) {
                $this->line("[DRY-RUN] {$comp->id} ({$comp->serie}-{$comp->numero}) fileName={$fileName}");
                continue;
            }

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
                Log::info("restaurante:reenviar-pendientes response comprobante {$comp->id}: " . json_encode($data));

                $estadosOk = ['PENDIENTE', 'aceptado', 'ACEPTADO'];
                $aceptada  = $response->successful() && isset($data['sunatResponse']);
                $pendiente = $response->successful() && isset($data['status']) && in_array($data['status'], $estadosOk);
                $pdfUrl    = $data['sunatResponse']['enlace_del_pdf'] ?? null;

                DB::table('comprobantes_sunat')->where('id', $comp->id)->update([
                    'aceptada_por_sunat'   => $aceptada ? 1 : 0,
                    'apisunat_document_id' => substr($data['documentId'] ?? '', 0, 100) ?: null,
                    'sunat_descripcion'    => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                    'enlace_pdf'           => $pdfUrl ?? $comp->enlace_pdf,
                    'estado'               => $aceptada ? 'aceptado' : ($pendiente ? 'pendiente' : 'rechazado'),
                    'updated_at'           => now(),
                ]);

                $this->info("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): " . ($aceptada ? 'ACEPTADO' : ($pendiente ? 'sigue PENDIENTE' : 'RECHAZADO')));
            } catch (\Throwable $e) {
                Log::error("restaurante:reenviar-pendientes error comprobante {$comp->id}: " . $e->getMessage());
                $this->error("Comprobante {$comp->id}: {$e->getMessage()}");
            }
        }

        $this->info('Reconciliación completada.');
        return self::SUCCESS;
    }

    private function numeroALetras($numero)
    {
        $entero  = (int) $numero;
        $decimal = round(($numero - $entero) * 100);
        return $this->enLetras($entero) . ' CON ' . str_pad((string) $decimal, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
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
        return (string) $n;
    }
}
