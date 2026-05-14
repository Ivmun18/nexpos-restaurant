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
}
