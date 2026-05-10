<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacturacionService
{
    public function emitir($venta, $empresa)
    {
        $venta->load('detalle');
        $esRus     = $empresa->regimen_tributario === 'RUS' || $empresa->zona_exonerada;
        $proveedor = $empresa->proveedor_facturacion ?? 'apisunat';

        $items = $venta->detalle->map(function($item) use ($esRus) {
            if ($esRus) {
                return [
                    'unidad_de_medida'           => 'NIU',
                    'codigo'                     => $item->codigo_producto ?? 'S/C',
                    'descripcion'                => $item->descripcion,
                    'cantidad'                   => $item->cantidad,
                    'valor_unitario'             => $item->precio_unitario,
                    'precio_unitario'            => $item->precio_unitario,
                    'subtotal'                   => round($item->precio_unitario * $item->cantidad, 2),
                    'igv'                        => 0,
                    'total'                      => round($item->precio_unitario * $item->cantidad, 2),
                    'tipo_de_igv'                => 8,
                    'descuento'                  => '0',
                    'anticipo_regularizacion'    => false,
                    'anticipo_documento_serie'   => '',
                    'anticipo_documento_numero'  => '',
                    'porcentaje_igv'             => 0,
                    'codigo_tipo_afectacion_igv' => '40',
                    'nombre_tributo'             => 'INA',
                ];
            }
            $valorUnitario = round($item->precio_unitario / 1.18, 4);
            $igvItem = round($valorUnitario * 0.18 * $item->cantidad, 2);
            return [
                'unidad_de_medida'           => 'NIU',
                'codigo'                     => $item->codigo_producto ?? 'S/C',
                'descripcion'                => $item->descripcion,
                'cantidad'                   => $item->cantidad,
                'valor_unitario'             => $valorUnitario,
                'precio_unitario'            => $item->precio_unitario,
                'subtotal'                   => round($valorUnitario * $item->cantidad, 2),
                'igv'                        => $igvItem,
                'total'                      => round($item->precio_unitario * $item->cantidad, 2),
                'tipo_de_igv'                => 1,
                'descuento'                  => '0',
                'anticipo_regularizacion'    => false,
                'anticipo_documento_serie'   => '',
                'anticipo_documento_numero'  => '',
                'porcentaje_igv'             => 18,
                'codigo_tipo_afectacion_igv' => '10',
                'nombre_tributo'             => 'IGV',
            ];
        })->toArray();

        try {
            if ($proveedor === 'apisunat') {
                $this->emitirApisunat($venta, $empresa, $items);
            } else {
                $this->emitirNubefact($venta, $empresa, $items, $esRus);
            }
        } catch (\Exception $e) {
            Log::error('Error emision: ' . $e->getMessage());
            $venta->update([
                'nubefact_estado' => 'error',
                'observaciones'   => $e->getMessage(),
            ]);
        }
    }

    private function emitirApisunat($venta, $empresa, $items)
    {
        $payload = [
            'documento'                   => $venta->tipo_comprobante === '01' ? 'factura' : 'boleta',
            'serie'                       => $venta->serie,
            'numero'                      => $venta->correlativo,
            'fecha_de_emision'            => now()->format('Y-m-d'),
            'moneda'                      => 'PEN',
            'tipo_operacion'              => '0101',
            'cliente_tipo_de_documento'   => $venta->tipo_comprobante === '01' ? '6' : '1',
            'cliente_numero_de_documento' => $venta->cliente_num_doc ?? '',
            'cliente_denominacion'        => $venta->cliente_razon_social ?? 'CLIENTES VARIOS',
            'cliente_direccion'           => '',
            'cliente_correo'              => $venta->cliente_email ?? '',
            'total_gravada'               => $venta->total_gravado ?? 0,
            'total_exonerada'             => $venta->total_exonerado ?? 0,
            'total_inafecta'              => $venta->total_inafecto ?? 0,
            'total_igv'                   => $venta->total_igv ?? 0,
            'total'                       => $venta->total,
            'items'                       => collect($items)->map(fn($i) => [
                'unidad_de_medida'           => $i['unidad_de_medida'],
                'descripcion'                => $i['descripcion'],
                'cantidad'                   => $i['cantidad'],
                'valor_unitario'             => $i['valor_unitario'],
                'porcentaje_igv'             => $i['porcentaje_igv'],
                'codigo_tipo_afectacion_igv' => $i['codigo_tipo_afectacion_igv'],
                'nombre_tributo'             => $i['nombre_tributo'],
            ])->toArray(),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $empresa->nubefact_token,
            'Content-Type'  => 'application/json',
        ])->post('https://api.apisunat.com/v1/personas/' . $empresa->ruc . '/documentos', $payload);

        if ($response->successful()) {
            $data = $response->json();
            $venta->update([
                'nubefact_id'     => $data['payload']['pdf'] ?? null,
                'nubefact_estado' => 'aceptado',
                'observaciones'   => json_encode($data),
            ]);
        } else {
            $venta->update([
                'nubefact_estado' => 'rechazado',
                'observaciones'   => json_encode($response->json()),
            ]);
        }
    }

    private function emitirNubefact($venta, $empresa, $items, $esRus)
    {
        $url = $empresa->nubefact_demo
            ? 'https://demo-api.nubefact.com/api/v1/'
            : 'https://api.nubefact.com/api/v1/';

        $payload = [
            'operacion'                         => 'generar_comprobante',
            'tipo_de_comprobante'               => $venta->tipo_comprobante === '01' ? 1 : 2,
            'serie'                             => $venta->serie,
            'numero'                            => $venta->correlativo,
            'sunat_transaction'                 => 1,
            'cliente_tipo_de_documento'         => $venta->tipo_comprobante === '01' ? 6 : 1,
            'cliente_numero_de_documento'       => $venta->cliente_num_doc ?? '',
            'cliente_denominacion'              => $venta->cliente_razon_social ?? 'CLIENTE',
            'cliente_direccion'                 => '',
            'cliente_email'                     => $venta->cliente_email ?? '',
            'fecha_de_emision'                  => now()->format('d-m-Y'),
            'hora_de_emision'                   => now()->format('H:i:s'),
            'moneda'                            => 1,
            'tipo_de_cambio'                    => '',
            'porcentaje_de_igv'                 => $esRus ? 0.0 : 18.0,
            'total_gravada'                     => $venta->total_gravado ?? 0,
            'total_exonerada'                   => $venta->total_exonerado ?? 0,
            'total_inafecta'                    => $venta->total_inafecto ?? 0,
            'total_igv'                         => $venta->total_igv ?? 0,
            'total'                             => $venta->total,
            'detalle'                           => $items,
            'enviar_automaticamente_a_la_sunat' => true,
            'enviar_automaticamente_al_cliente' => !empty($venta->cliente_email),
            'codigo_unico'                      => $venta->id,
            'condiciones_de_pago'               => 'Contado',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token token=' . $empresa->nubefact_token,
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            $venta->update([
                'nubefact_id'     => $data['enlace_del_pdf'] ?? null,
                'nubefact_estado' => 'aceptado',
                'observaciones'   => json_encode($data),
            ]);
        } else {
            $venta->update([
                'nubefact_estado' => 'rechazado',
                'observaciones'   => json_encode($response->json()),
            ]);
        }
    }
}
