<?php

namespace App\Services\Facturacion;

interface FacturacionInterface
{
    /**
     * Emitir una boleta electrónica
     */
    public function emitirBoleta(array $datos): array;

    /**
     * Emitir una factura electrónica
     */
    public function emitirFactura(array $datos): array;

    /**
     * Emitir una nota de crédito
     */
    public function emitirNotaCredito(array $datos): array;

    /**
     * Consultar estado de un comprobante en SUNAT
     */
    public function consultarEstado(string $serie, string $numero): array;

    /**
     * Probar conexión con el proveedor
     */
    public function probarConexion(): array;
}
