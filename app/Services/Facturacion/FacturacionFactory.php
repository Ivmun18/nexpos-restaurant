<?php

namespace App\Services\Facturacion;

use App\Models\Empresa;
use Exception;

class FacturacionFactory
{
    /**
     * Crear instancia del servicio de facturación según configuración de la empresa
     */
    public static function crear(?Empresa $empresa = null): FacturacionInterface
    {
        // Si no se pasa empresa, usar la empresa del usuario autenticado
        if (!$empresa) {
            $empresa = auth()->user()?->empresa;
        }

        if (!$empresa) {
            throw new Exception('No se pudo determinar la empresa para facturación');
        }

        // Obtener el proveedor configurado (por defecto 'nubefact')
        $proveedor = $empresa->proveedor_facturacion ?? 'nubefact';

        return match($proveedor) {
            'apisunat' => self::crearAPISunat($empresa),
            'nubefact' => self::crearNubefact($empresa),
            default => throw new Exception("Proveedor de facturación '$proveedor' no soportado"),
        };
    }

    /**
     * Crear servicio APISunat
     */
    private static function crearAPISunat(Empresa $empresa): APISunatService
    {
        $token = $empresa->apisunat_token;
        $ruc = $empresa->apisunat_ruc ?? $empresa->ruc;
        $usuarioSol = $empresa->apisunat_usuario_sol;
        $claveSol = $empresa->apisunat_clave_sol;
        $demo = $empresa->apisunat_demo ?? true;

        if (!$token || !$ruc || !$usuarioSol || !$claveSol) {
            throw new Exception('Configuración de APISunat incompleta. Verifica token, RUC, usuario SOL y clave SOL.');
        }

        return new APISunatService($token, $ruc, $usuarioSol, $claveSol, $demo);
    }

    /**
     * Crear servicio Nubefact
     */
    private static function crearNubefact(Empresa $empresa): NubefactService
    {
        $token = $empresa->nubefact_token;
        $ruc = $empresa->ruc;
        $demo = $empresa->nubefact_demo ?? true;

        if (!$token || !$ruc) {
            throw new Exception('Configuración de Nubefact incompleta. Verifica token y RUC.');
        }

        return new NubefactService($token, $ruc, $demo);
    }
}
