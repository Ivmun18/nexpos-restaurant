<?php

namespace App\Services\Sunat;

use App\Models\Venta;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;

class XmlGeneratorService
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

    public function generarFactura(Venta $venta): array
    {
        $venta->load('detalle');

        $company = $this->getCompany();

        $invoice = new Invoice();
        $invoice
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($venta->tipo_comprobante)
            ->setSerie($venta->serie)
            ->setCorrelativo(str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision(new \DateTime($venta->fecha_emision))
            ->setTipoMoneda($venta->moneda)
            ->setCompany($company)
            ->setClient($this->getCliente($venta))
            ->setMtoOperGravadas($venta->total_gravado)
            ->setMtoOperExoneradas($venta->total_exonerado)
            ->setMtoIGV($venta->total_igv)
            ->setTotalImpuestos($venta->total_igv)
            ->setValorVenta($venta->total_gravado + $venta->total_exonerado)
            ->setMtoImpVenta($venta->total)
            ->setDetails($this->getDetalle($venta))
            ->setLegends($this->getLegends($venta->total));

        $xml = $this->see->getXmlSigned($invoice);

        return [
            'xml'            => $xml,
            'nombre_archivo' => $this->getNombreArchivo($venta),
        ];
    }

    private function getCompany(): Company
    {
        $address = new Address();
        $address
            ->setUbigueo('150101')
            ->setDepartamento('LIMA')
            ->setProvincia('LIMA')
            ->setDistrito('LIMA')
            ->setUrbanizacion('NONE')
            ->setDireccion('Av. Principal 123');

        $company = new Company();
        $company
            ->setRuc('20123456789')
            ->setRazonSocial('MI EMPRESA SAC')
            ->setNombreComercial('MI EMPRESA')
            ->setAddress($address);

        return $company;
    }

    private function getCliente(Venta $venta): \Greenter\Model\Client\Client
    {
        $client = new \Greenter\Model\Client\Client();
        $client
            ->setTipoDoc($venta->cliente_tipo_doc ?? '0')
            ->setNumDoc($venta->cliente_num_doc ?? '')
            ->setRznSocial($venta->cliente_razon_social ?? 'CLIENTES VARIOS');

        return $client;
    }

    private function getDetalle(Venta $venta): array
    {
        $detalles = [];
        foreach ($venta->detalle as $item) {
            $detail = new SaleDetail();
            $detail
                ->setCodProducto($item->codigo_producto)
                ->setUnidad($item->unidad_medida)
                ->setDescripcion($item->descripcion)
                ->setCantidad($item->cantidad)
                ->setMtoValorUnitario($item->valor_unitario)
                ->setMtoValorVenta($item->total_valor_venta)
                ->setMtoBaseIgv($item->total_valor_venta)
                ->setPorcentajeIgv(18.00)
                ->setIgv($item->total_igv)
                ->setTipAfeIgv($item->tipo_afectacion_igv)
                ->setTotalImpuestos($item->total_igv)
                ->setMtoPrecioUnitario($item->precio_unitario);

            $detalles[] = $detail;
        }
        return $detalles;
    }

    private function getLegends(float $total): array
    {
        $legend = new Legend();
        $legend
            ->setCode('1000')
            ->setValue($this->numeroALetras($total));

        return [$legend];
    }

private function getCertificate(): string
    {
        $pemPath = storage_path('app/private/sunat/certificados/certificado.pem');
        if (file_exists($pemPath) && filesize($pemPath) > 100) {
            return file_get_contents($pemPath);
        }
        throw new \Exception('No se encontró certificado digital.');
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

    private function numeroALetras(float $numero): string
    {
        $entero   = (int) $numero;
        $decimales= (int) round(($numero - $entero) * 100);
        return strtoupper("SON {$entero} CON {$decimales}/100 SOLES");
    }
}