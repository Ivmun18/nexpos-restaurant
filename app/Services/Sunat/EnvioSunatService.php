<?php

namespace App\Services\Sunat;

use App\Models\Venta;
use App\Models\XmlDocumento;
use App\Models\SunatRespuesta;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

class EnvioSunatService
{
    private See $see;

    public function __construct()
    {
        $this->see = new See();
        $this->see->setService(SunatEndpoints::FE_BETA);
        $this->see->setCertificate($this->getCertificate());
        $this->see->setCredentials(
            config('sunat.sol_usuario'),
            config('sunat.sol_clave')
        );
    }

    public function enviar(Venta $venta): array
    {
        try {
            $venta->load('detalle');

            $invoice = $this->buildInvoice($venta);

            // Generar XML firmado
            $xml    = $this->see->getXmlSigned($invoice);
            $nombre = $this->getNombreArchivo($venta);

            // Guardar XML
            XmlDocumento::updateOrCreate(
                [
                    'empresa_id'       => 1,
                    'tipo_comprobante' => $venta->tipo_comprobante,
                    'numero_completo'  => $venta->numero_completo,
                ],
                [
                    'referencia_id'    => $venta->id,
                    'referencia_tabla' => 'ventas',
                    'xml_firmado'      => $xml,
                    'nombre_archivo'   => $nombre,
                    'zip_nombre'       => $nombre . '.zip',
                ]
            );

            // Enviar a SUNAT
            $result = $this->see->send($invoice);

            $exitoso  = $result && $result->isSuccess();
            $cdrResp  = $result ? $result->getCdrResponse() : null;
            $codigo   = $cdrResp ? $cdrResp->getCode() : null;
            $descripcion = $cdrResp ? $cdrResp->getDescription() : ($result && $result->getError() ? $result->getError()->getMessage() : 'Sin respuesta');

            // Guardar respuesta CDR
            SunatRespuesta::create([
                'empresa_id'           => 1,
                'numero_completo'      => $venta->numero_completo,
                'tipo_comprobante'     => $venta->tipo_comprobante,
                'referencia_id'        => $venta->id,
                'referencia_tabla'     => 'ventas',
                'codigo_respuesta'     => $codigo,
                'descripcion_respuesta'=> $descripcion,
                'fecha_envio'          => now(),
                'fecha_respuesta'      => now(),
                'exitoso'              => $exitoso,
                'error_tecnico'        => $exitoso ? null : $descripcion,
            ]);

            // Actualizar estado venta
            $venta->update(['estado' => $exitoso ? 'aceptado' : 'rechazado']);

            return [
                'exitoso'     => $exitoso,
                'codigo'      => $codigo,
                'descripcion' => $descripcion,
            ];

        } catch (\Exception $e) {
            SunatRespuesta::create([
                'empresa_id'       => 1,
                'numero_completo'  => $venta->numero_completo,
                'tipo_comprobante' => $venta->tipo_comprobante,
                'referencia_id'    => $venta->id,
                'referencia_tabla' => 'ventas',
                'fecha_envio'      => now(),
                'exitoso'          => false,
                'error_tecnico'    => $e->getMessage(),
            ]);

            return [
                'exitoso'     => false,
                'codigo'      => null,
                'descripcion' => $e->getMessage(),
            ];
        }
    }

    private function buildInvoice(Venta $venta): Invoice
    {
        $address = (new Address())
            ->setUbigueo('150101')
            ->setDepartamento('LIMA')
            ->setProvincia('LIMA')
            ->setDistrito('LIMA')
            ->setUrbanizacion('NONE')
            ->setDireccion('Av. Principal 123');

        $company = (new Company())
            ->setRuc('20123456789')
            ->setRazonSocial('MI EMPRESA SAC')
            ->setNombreComercial('MI EMPRESA')
            ->setAddress($address);

        $client = (new Client())
            ->setTipoDoc($venta->cliente_tipo_doc ?? '0')
            ->setNumDoc($venta->cliente_num_doc ?? '')
            ->setRznSocial($venta->cliente_razon_social ?? 'CLIENTES VARIOS');

        $details = [];
        foreach ($venta->detalle as $item) {
            $esGravado  = $item->tipo_afectacion_igv === '10';
            $porcentaje = $esGravado ? 18.00 : 0.00;
            $igv        = $esGravado ? floatval($item->total_igv) : 0.00;
            $baseIgv    = floatval($item->total_valor_venta);

            $details[] = (new SaleDetail())
                ->setCodProducto($item->codigo_producto)
                ->setUnidad($item->unidad_medida)
                ->setDescripcion($item->descripcion)
                ->setCantidad(floatval($item->cantidad))
                ->setMtoValorUnitario(floatval($item->valor_unitario))
                ->setMtoValorVenta(floatval($item->total_valor_venta))
                ->setMtoBaseIgv($baseIgv)
                ->setPorcentajeIgv($porcentaje)
                ->setIgv($igv)
                ->setTipAfeIgv($item->tipo_afectacion_igv)
                ->setTotalImpuestos($igv)
                ->setMtoPrecioUnitario(floatval($item->precio_unitario));
        }

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($this->numeroALetras($venta->total));

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($venta->tipo_comprobante)
            ->setSerie($venta->serie)
            ->setCorrelativo(str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision(new \DateTime($venta->fecha_emision))
            ->setTipoMoneda($venta->moneda)
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(floatval($venta->total_gravado))
            ->setMtoOperExoneradas(floatval($venta->total_exonerado))
            ->setMtoIGV(floatval($venta->total_igv))
            ->setTotalImpuestos(floatval($venta->total_igv))
            ->setValorVenta(floatval($venta->total_gravado) + floatval($venta->total_exonerado))
            ->setMtoImpVenta(floatval($venta->total))
            ->setDetails($details)
            ->setLegends([$legend]);

        return $invoice;
    }

    private function getNombreArchivo(Venta $venta): string
    {
        return sprintf(
            '20123456789-%s-%s-%s',
            $venta->tipo_comprobante,
            $venta->serie,
            str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT)
        );
    }

    private function numeroALetras(float $total): string
    {
        $entero    = (int) $total;
        $decimales = (int) round(($total - $entero) * 100);
        return strtoupper("SON {$entero} CON {$decimales}/100 SOLES");
    }

    private function getCertificate(): string
    {
        $pemPath = storage_path('app/private/sunat/certificados/certificado.pem');
        if (file_exists($pemPath) && filesize($pemPath) > 100) {
            return file_get_contents($pemPath);
        }
        throw new \Exception('No se encontró certificado digital.');
    }
}