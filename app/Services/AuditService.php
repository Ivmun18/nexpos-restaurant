<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function registrar(
        $accion,
        $modulo,
        $modelo = null,
        $registroId = null,
        $cambiosAnteriores = null,
        $cambiosNuevos = null,
        $detalles = null
    ) {
        try {
            $empresaId = auth()->user()?->empresa_id ?? null;
            
            if (!$empresaId) {
                return;
            }

            AuditLog::create([
                'empresa_id' => $empresaId,
                'usuario_id' => Auth::id(),
                'accion' => $accion,
                'modulo' => $modulo,
                'modelo' => $modelo,
                'registro_id' => $registroId,
                'cambios_anteriores' => $cambiosAnteriores ? json_encode($cambiosAnteriores) : null,
                'cambios_nuevos' => $cambiosNuevos ? json_encode($cambiosNuevos) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'detalles' => $detalles,
            ]);
        } catch (\Exception $e) {
            // Silenciar errores de auditoría para no afectar la aplicación
            \Log::error('Error registrando auditoría: ' . $e->getMessage());
        }
    }

    public static function registrarCompra($compra, $accion = 'create', $cambiosAnteriores = null)
    {
        self::registrar(
            $accion,
            'Compras',
            'Compra',
            $compra->id,
            $cambiosAnteriores,
            $compra->attributesToArray(),
            "Compra a {$compra->proveedor->razon_social}"
        );
    }

    public static function registrarPago($pago, $accion = 'create')
    {
        self::registrar(
            $accion,
            'Pagos',
            'PagoProveedor',
            $pago->id,
            null,
            $pago->attributesToArray(),
            "Pago S/ {$pago->monto}"
        );
    }

    public static function registrarProducto($producto, $accion = 'create', $cambiosAnteriores = null)
    {
        self::registrar(
            $accion,
            'Productos',
            'Producto',
            $producto->id,
            $cambiosAnteriores,
            $producto->attributesToArray(),
            "Producto: {$producto->descripcion}"
        );
    }
}
