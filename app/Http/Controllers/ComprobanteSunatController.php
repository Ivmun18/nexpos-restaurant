<?php

namespace App\Http\Controllers;

use App\Models\ComprobanteSunat;
use App\Models\CajaRestaurante;
use App\Services\NubefactService;
use Illuminate\Http\Request;
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
            ->with(['caja.mesa'])
            ->when($request->tipo, function ($query, $tipo) {
                $query->where('tipo_comprobante', $tipo);
            })
            ->when($request->desde, function ($query, $desde) {
                $query->whereDate('fecha_emision', '>=', $desde);
            })
            ->when($request->hasta, function ($query, $hasta) {
                $query->whereDate('fecha_emision', '<=', $hasta);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Comprobantes/Index', [
            'comprobantes' => $comprobantes,
            'filtros' => [
                'tipo' => $request->tipo,
                'desde' => $request->desde,
                'hasta' => $request->hasta,
            ],
        ]);
    }

    /**
     * Formulario para emitir comprobante desde caja
     */
    public function crear(CajaRestaurante $caja): Response
    {
        // Verificar que la caja pertenece a la empresa del usuario
        if ($caja->mesa && auth()->user()->empresa_id != auth()->user()->empresa_id) {
            abort(403);
        }

        // Verificar que no tenga comprobante ya emitido
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
            'cliente_tipo_documento' => 'required|in:1,6',
            'cliente_documento' => 'required|string|max:11',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_email' => 'nullable|email',
        ]);

        $empresa = auth()->user()->empresa;

        // Calcular montos según régimen tributario
        $total = $caja->total;
        $regimen = $empresa->regimen_tributario ?? 'GENERAL';
        $esRus = in_array($regimen, ['RUS', 'EXONERADO']) || $empresa->zona_exonerada;

        if ($esRus) {
            $totalGravada = 0;
            $totalIgv     = 0;
            $totalInafecto = $total;
        } elseif ($empresa->zona_exonerada) {
            $totalGravada  = 0;
            $totalIgv      = 0;
            $totalInafecto = 0;
            $totalExonerado = $total;
        } else {
            $totalGravada  = round($total / 1.18, 2);
            $totalIgv      = round($total - $totalGravada, 2);
            $totalInafecto = 0;
        }

        // Si no hay token, guardar comprobante local sin enviar a SUNAT
        if (!$empresa->nubefact_token) {
            $serie = $empresa->serie_boleta ?? 'B001';
            $numero = ComprobanteSunat::where('empresa_id', $empresa->id)->where('serie', $serie)->max('numero') + 1;
            ComprobanteSunat::create([
                'empresa_id'              => $empresa->id,
                'caja_restaurante_id'     => $caja->id,
                'tipo_comprobante'        => '03',
                'serie'                   => $serie,
                'numero'                  => $numero,
                'fecha_emision'           => now(),
                'cliente_tipo_documento'  => $request->cliente_tipo_documento,
                'cliente_numero_documento'=> $request->cliente_documento,
                'cliente_nombre'          => $request->cliente_nombre,
                'cliente_email'           => $request->cliente_email ?? '',
                'total_gravada'           => $totalGravada,
                'total_igv'               => $totalIgv,
                'total_inafecta'          => $totalInafecto ?? 0,
                'total'                   => $total,
                'estado'                  => 'emitido',
                'enlace_pdf'              => null,
            ]);
            return redirect()->route('comprobantes.index')->with('success', 'Boleta registrada localmente (sin token SUNAT).');
        }

        $nubefact = new NubefactService($empresa);

        // Preparar items
        $items = [[
            'unidad_de_medida' => 'NIU',
            'codigo' => '001',
            'descripcion' => 'Consumo en Mesa ' . $caja->mesa->numero,
            'cantidad' => 1,
            'valor_unitario' => $totalGravada,
            'precio_unitario' => $total,
            'subtotal' => $totalGravada,
            'tipo_de_igv' => 1, // Gravado
            'igv' => $totalIgv,
            'total' => $total,
        ]];

        // Emitir a Nubefact
        $resultado = $nubefact->emitirBoleta([
            'cliente_tipo_documento' => $request->cliente_tipo_documento,
            'cliente_documento' => $request->cliente_documento,
            'cliente_nombre' => $request->cliente_nombre,
            'cliente_email' => $request->cliente_email,
            'total_gravada' => $totalGravada,
            'total_igv' => $totalIgv,
            'total' => $total,
            'items' => $items,
        ]);

        if (!$resultado['success']) {
            return redirect()->back()->with('error', 'Error al emitir: ' . ($resultado['error'] ?? 'Desconocido'));
        }

        // Guardar comprobante en BD
        $comprobante = ComprobanteSunat::create([
            'empresa_id' => $empresa->id,
            'caja_restaurante_id' => $caja->id,
            'tipo_comprobante' => '03',
            'serie' => $resultado['serie'],
            'numero' => $resultado['numero'],
            'fecha_emision' => now(),
            'cliente_tipo_documento' => $request->cliente_tipo_documento,
            'cliente_numero_documento' => $request->cliente_documento,
            'cliente_nombre' => $request->cliente_nombre,
            'cliente_email' => $request->cliente_email,
            'total_gravada' => $totalGravada,
            'total_igv' => $totalIgv,
            'total' => $total,
            'aceptada_por_sunat' => $resultado['aceptada_por_sunat'],
            'codigo_hash' => $resultado['codigo_hash'],
            'enlace_pdf' => $resultado['enlace_pdf'],
            'enlace_xml' => $resultado['enlace_xml'],
            'estado' => $resultado['aceptada_por_sunat'] ? 'aceptado' : 'emitido',
        ]);

        return redirect()->route('comprobantes.show', $comprobante)
            ->with('success', '¡Boleta emitida correctamente!');
    }

    /**
     * Emitir factura
     */
    public function emitirFactura(Request $request, CajaRestaurante $caja)
    {
        $request->validate([
            'cliente_ruc' => 'required|string|size:11',
            'cliente_razon_social' => 'required|string|max:255',
            'cliente_direccion' => 'required|string|max:255',
            'cliente_email' => 'nullable|email',
        ]);

        $empresa = auth()->user()->empresa;
        $nubefact = new NubefactService($empresa);

        // Calcular montos
        $total = $caja->total;
        $totalGravada = round($total / 1.18, 2);
        $totalIgv = $total - $totalGravada;

        // Preparar items
        $items = [[
            'unidad_de_medida' => 'NIU',
            'codigo' => '001',
            'descripcion' => 'Consumo en Mesa ' . $caja->mesa->numero,
            'cantidad' => 1,
            'valor_unitario' => $totalGravada,
            'precio_unitario' => $total,
            'subtotal' => $totalGravada,
            'tipo_de_igv' => 1,
            'igv' => $totalIgv,
            'total' => $total,
        ]];

        // Emitir a Nubefact
        $resultado = $nubefact->emitirFactura([
            'cliente_ruc' => $request->cliente_ruc,
            'cliente_razon_social' => $request->cliente_razon_social,
            'cliente_direccion' => $request->cliente_direccion,
            'cliente_email' => $request->cliente_email,
            'total_gravada' => $totalGravada,
            'total_igv' => $totalIgv,
            'total' => $total,
            'items' => $items,
        ]);

        if (!$resultado['success']) {
            return redirect()->back()->with('error', 'Error al emitir: ' . ($resultado['error'] ?? 'Desconocido'));
        }

        // Guardar comprobante
        $comprobante = ComprobanteSunat::create([
            'empresa_id' => $empresa->id,
            'caja_restaurante_id' => $caja->id,
            'tipo_comprobante' => '01',
            'serie' => $resultado['serie'],
            'numero' => $resultado['numero'],
            'fecha_emision' => now(),
            'cliente_tipo_documento' => '6',
            'cliente_numero_documento' => $request->cliente_ruc,
            'cliente_nombre' => $request->cliente_razon_social,
            'cliente_direccion' => $request->cliente_direccion,
            'cliente_email' => $request->cliente_email,
            'total_gravada' => $totalGravada,
            'total_igv' => $totalIgv,
            'total' => $total,
            'aceptada_por_sunat' => $resultado['aceptada_por_sunat'],
            'codigo_hash' => $resultado['codigo_hash'],
            'enlace_pdf' => $resultado['enlace_pdf'],
            'enlace_xml' => $resultado['enlace_xml'],
            'estado' => $resultado['aceptada_por_sunat'] ? 'aceptado' : 'emitido',
        ]);

        return redirect()->route('comprobantes.show', $comprobante)
            ->with('success', '¡Factura emitida correctamente!');
    }

    /**
     * Ver detalle de comprobante
     */
    public function show(ComprobanteSunat $comprobante): Response
    {
        $comprobante->load(['caja.mesa', 'empresa']);

        return Inertia::render('Comprobantes/Show', [
            'comprobante' => $comprobante,
        ]);
    }
}
