<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoSeguimiento;
use App\Models\ActoDato;
use App\Models\ActoRequisito;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActoNotarialController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $buscar    = $request->get('buscar', '');
        $tipo      = $request->get('tipo', '');
        $estado    = $request->get('estado', '');
        $desde     = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta     = $request->get('hasta', now()->addDay()->toDateString());

        $query = ActoNotarial::with(['cliente', 'usuario'])
            ->where('empresa_id', $empresaId)
            ->whereBetween('fecha_ingreso', [$desde, $hasta])
            ->orderBy('created_at', 'desc');

        if ($buscar) $query->where(function($q) use ($buscar) {
            $q->where('numero_expediente', 'like', "%{$buscar}%")
              ->orWhere('asunto', 'like', "%{$buscar}%")
              ->orWhere('partes_intervinientes', 'like', "%{$buscar}%");
        });
        if ($tipo)   $query->where('tipo_acto', $tipo);
        if ($estado) $query->where('estado', $estado);

        $actos = $query->get();

        $resumen = [
            'total'       => $actos->count(),
            'pendientes'  => $actos->where('estado', 'pendiente')->count(),
            'en_proceso'  => $actos->where('estado', 'en_proceso')->count(),
            'finalizados' => $actos->where('estado', 'finalizado')->count(),
            'por_cobrar'  => round($actos->sum('monto_cobrar') - $actos->sum('monto_pagado'), 2),
            'cobrado'     => round($actos->sum('monto_pagado'), 2),
        ];

        $clientes = Cliente::where('empresa_id', $empresaId)->orderBy('razon_social')->get(['id', 'razon_social', 'numero_documento']);

        return Inertia::render('Notaria/Actos/Index', [
            'actos'    => $actos,
            'resumen'  => $resumen,
            'clientes' => $clientes,
            'filtros'  => compact('buscar', 'tipo', 'estado', 'desde', 'hasta'),
        ]);
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $request->validate([
            'tipo_acto'             => 'required|string',
            'asunto'                => 'required|string|max:300',
            'fecha_ingreso'         => 'required|date',
            'monto_cobrar'          => 'required|numeric|min:0',
            'partes_intervinientes' => 'nullable|string',
            'observaciones'         => 'nullable|string',
        ]);

        $acto = ActoNotarial::create([
            'empresa_id'            => $empresaId,
            'numero_expediente'     => ActoNotarial::generarNumero($empresaId),
            'tipo_acto'             => $request->tipo_acto,
            'asunto'                => $request->asunto,
            'cliente_id'            => $request->cliente_id,
            'usuario_id'            => auth()->id(),
            'estado'                => 'pendiente',
            'fecha_ingreso'         => $request->fecha_ingreso,
            'fecha_entrega'         => $request->fecha_entrega,
            'monto_cobrar'          => $request->monto_cobrar,
            'monto_pagado'          => 0,
            'estado_pago'           => 'pendiente',
            'partes_intervinientes' => $request->partes_intervinientes,
            'observaciones'         => $request->observaciones,
        ]);

        ActoSeguimiento::create([
            'acto_id'     => $acto->id,
            'usuario_id'  => auth()->id(),
            'estado_nuevo'=> 'pendiente',
            'comentario'  => 'Expediente creado',
        ]);

        // Guardar datos de plantilla si vienen
        if ($request->has('datos') && is_array($request->datos)) {
            foreach ($request->datos as $campo => $valor) {
                if (!empty($valor)) {
                    ActoDato::create([
                        'acto_id' => $acto->id,
                        'campo'   => $campo,
                        'valor'   => $valor,
                    ]);
                }
            }
        }

        return back()->with('success', 'Expediente ' . $acto->numero_expediente . ' creado correctamente.');
    }

    public function show(ActoNotarial $acto)
    {
        $acto->load(['cliente', 'usuario', 'documentos.usuario', 'seguimientos.usuario', 'datos', 'requisitos.user', 'partes']);
        $datosMapa = $acto->datos->pluck('valor', 'campo');
        $empresa = auth()->user()->empresa;
        $vendedor = [
            'vendedor_tipo'              => $empresa->minuta_vendedor_tipo ?? 'empresa',
            'vendedor_razon_social'      => $empresa->minuta_vendedor_razon_social ?? '',
            'vendedor_ruc'               => $empresa->minuta_vendedor_ruc ?? '',
            'vendedor_domicilio'         => $empresa->minuta_vendedor_domicilio ?? '',
            'vendedor_partida_registral' => $empresa->minuta_vendedor_partida ?? '',
            'representante_cargo'        => $empresa->minuta_representante_cargo ?? 'Gerente General',
            'representante_nombre'       => $empresa->minuta_representante_nombre ?? '',
            'representante_dni'          => $empresa->minuta_representante_dni ?? '',
            'representante_estado_civil' => $empresa->minuta_representante_estado_civil ?? 'soltero',
            'representante_profesion'    => $empresa->minuta_representante_profesion ?? '',
            'representante_domicilio'    => $empresa->minuta_representante_domicilio ?? '',
            'ciudad'                     => $empresa->minuta_ciudad ?? 'Huánuco',
        ];
        return Inertia::render('Notaria/Actos/Show', ['acto' => $acto, 'datos' => $datosMapa, 'vendedor' => $vendedor]);
    }

    public function seguimiento(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $actos = ActoNotarial::with(['cliente', 'usuario', 'requisitos', 'seguimientos.usuario'])
            ->where('empresa_id', $empresaId)
            ->whereIn('estado', ['pendiente', 'en_proceso', 'finalizado'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'                    => $a->id,
                'numero_expediente'     => $a->numero_expediente,
                'tipo_acto'             => $a->tipo_acto,
                'asunto'                => $a->asunto,
                'partes_intervinientes' => $a->partes_intervinientes,
                'estado'                => $a->estado,
                'estado_pago'           => $a->estado_pago,
                'fecha_ingreso'         => $a->fecha_ingreso,
                'fecha_entrega'         => $a->fecha_entrega,
                'monto_cobrar'          => $a->monto_cobrar,
                'monto_pagado'          => $a->monto_pagado,
                'usuario'               => $a->usuario?->name,
                'requisitos_total'      => $a->requisitos->count(),
                'requisitos_pendientes' => $a->requisitos->where('entregado', false)->count(),
                'requisitos_entregados' => $a->requisitos->where('entregado', true)->count(),
                'requisitos_faltantes'  => $a->requisitos->where('entregado', false)->values(),
                'requisitos_ok'         => $a->requisitos->where('entregado', true)->values(),
                'ultimo_seguimiento'    => $a->seguimientos->last() ? [
                    'estado_nuevo' => $a->seguimientos->last()->estado_nuevo,
                    'comentario'   => $a->seguimientos->last()->comentario,
                    'created_at'   => $a->seguimientos->last()->created_at,
                    'usuario'      => $a->seguimientos->last()->user?->name,
                ] : null,
            ]);

        return Inertia::render('Notaria/Seguimiento/Index', [
            'pendientes'  => $actos->where('estado', 'pendiente')->values(),
            'en_proceso'  => $actos->where('estado', 'en_proceso')->values(),
            'finalizados' => $actos->where('estado', 'finalizado')->values(),
            'todos'       => $actos->values(),
        ]);
    }

    public function agregarRequisito(Request $request, ActoNotarial $acto)
    {
        $request->validate(['documento' => 'required|string|max:200']);
        ActoRequisito::create([
            'acto_id'   => $acto->id,
            'documento' => $request->documento,
            'entregado' => false,
            'user_id'   => auth()->id(),
        ]);
        return back()->with('success', 'Requisito agregado.');
    }

    public function toggleRequisito(Request $request, ActoRequisito $requisito)
    {
        $requisito->update([
            'entregado'   => !$requisito->entregado,
            'observacion' => $request->observacion,
            'user_id'     => auth()->id(),
        ]);
        return back()->with('success', 'Requisito actualizado.');
    }

    public function eliminarRequisito(ActoRequisito $requisito)
    {
        $requisito->delete();
        return back()->with('success', 'Requisito eliminado.');
    }

    public function guardarDatos(Request $request, ActoNotarial $acto)
    {
        $request->validate(['datos' => 'required|array']);

        foreach ($request->datos as $campo => $valor) {
            ActoDato::updateOrCreate(
                ['acto_id' => $acto->id, 'campo' => $campo],
                ['valor'   => $valor]
            );
        }

        return back()->with('success', 'Datos guardados correctamente.');
    }

    public function cambiarEstado(Request $request, ActoNotarial $acto)
    {
        $request->validate([
            'estado'     => 'required|in:pendiente,en_proceso,finalizado,cancelado',
            'comentario' => 'nullable|string|max:500',
        ]);

        $acto->update(['estado' => $request->estado]);

        ActoSeguimiento::create([
            'acto_id'     => $acto->id,
            'usuario_id'  => auth()->id(),
            'estado_nuevo'=> $request->estado,
            'comentario'  => $request->comentario,
        ]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function registrarPago(Request $request, ActoNotarial $acto)
    {
        $request->validate(['monto' => 'required|numeric|min:0.01']);

        $nuevoPagado = $acto->monto_pagado + $request->monto;
        $estadoPago  = $nuevoPagado >= $acto->monto_cobrar ? 'pagado' : 'parcial';

        $acto->update(['monto_pagado' => $nuevoPagado, 'estado_pago' => $estadoPago]);

        $sesion = \App\Models\SesionCaja::join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')->where('sesiones_caja.estado', 'abierta')->where('caja.empresa_id', auth()->user()->empresa->id)->select('sesiones_caja.*')->first();
        if ($sesion) {
            \App\Models\CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => 'Cobro ' . $acto->numero_expediente . ' - ' . $acto->asunto,
                'referencia_id'=> $acto->id,
                'monto'        => $request->monto,
            ]);
        }

        return back()->with('success', 'Pago de S/ ' . $request->monto . ' registrado.');
    }
    public function generarDocumento(Request $request, ActoNotarial $acto, string $tipo)
    {
        $metodos = [
            'minuta-compraventa'    => 'generarMinutaCompraventa',
            'testimonio-compraventa'=> 'generarTestimonioCompraventa',
            'parte-compraventa'     => 'generarParteCompraventa',
        ];

        if (!isset($metodos[$tipo])) {
            return response()->json(['error' => 'Tipo de documento no válido'], 404);
        }

        return $this->{$metodos[$tipo]}($request, $acto);
    }

    public function generarMinutaCompraventa(Request $request, ActoNotarial $acto)
    {
        $empresa = auth()->user()->empresa;

        // Datos del formulario
        $d = $request->all();

        // Guardar/actualizar datos si se solicita
        if ($request->input('guardar_datos')) {
            $camposMinuta = ['comprador_nombre','comprador_dni','comprador_estado_civil','comprador_profesion','comprador_domicilio','es_bien_futuro','predio_descripcion','predio_partida','ciudad','proyecto_descripcion','proyecto_municipalidad','proyecto_expediente','proyecto_fecha','proyecto_arquitecto','plazo_anos','lote_descripcion','lote_area','lote_area_letras','lindero_frente','medida_frente','lindero_derecha','medida_derecha','lindero_izquierda','medida_izquierda','lindero_fondo','medida_fondo','precio_total','precio_total_letras','forma_pago_detalle','fecha_minuta'];
            foreach ($camposMinuta as $campo) {
                $valor = $request->input($campo, '');
                \DB::table('acto_datos')->updateOrInsert(
                    ['acto_id' => $acto->id, 'campo' => $campo],
                    ['valor' => is_array($valor) ? json_encode($valor) : (string)$valor, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        // Generar HTML de la minuta
        $html = view('notaria.minuta-compraventa', [
            'acto'    => $acto,
            'empresa' => $empresa,
            'd'       => $d,
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'      => 'Verdana',
                'isRemoteEnabled'  => false,
                'dpi'              => 96,
                'defaultMediaType' => 'print',
                'isPhpEnabled'     => true,
                'margin_top'       => 113.4, // 4cm = 113.4pt (1pt = 1/72 inch, 1cm = 28.35pt)
                'margin_right'     => 85.05, // 3cm
                'margin_bottom'    => 70.87, // 2.5cm
                'margin_left'      => 85.05, // 3cm
            ]);

        $filename = 'Minuta-CompraVenta-' . ($acto->numero_expediente ?? $acto->id) . '.pdf';

        return $pdf->download($filename);
    }

    public function crearConMinuta(Request $request)
    {
        $empresa = auth()->user()->empresa;
        $datos = $request->input('datos', []);

        // Crear expediente
        $numeroExpediente = 'EXP-' . date('Y') . '-' . str_pad(
            (\DB::table('actos_notariales')->where('empresa_id', $empresa->id)->count() + 1),
            5, '0', STR_PAD_LEFT
        );

        $actoId = \DB::table('actos_notariales')->insertGetId([
            'empresa_id'            => $empresa->id,
            'usuario_id'            => auth()->id(),
            'numero_expediente'     => $numeroExpediente,
            'tipo_acto'             => $request->tipo_acto,
            'asunto'                => $request->asunto,
            'partes_intervinientes' => ($datos['comprador_nombre'] ?? '') . ' / ' . ($empresa->minuta_vendedor_razon_social ?? $empresa->razon_social),
            'monto_cobrar'          => $request->monto_cobrar,
            'fecha_ingreso'         => $request->fecha_ingreso ?? now()->toDateString(),
            'fecha_entrega'         => $request->fecha_entrega ?? null,
            'estado'                => 'pendiente',
            'estado_pago'           => 'pendiente',
            'observaciones'         => $request->observaciones ?? null,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Guardar datos específicos
        if (!empty($datos)) {
            foreach ($datos as $campo => $valor) {
                \DB::table('acto_datos')->insert([
                    'acto_id'    => $actoId,
                    'campo'      => $campo,
                    'valor'      => is_array($valor) ? json_encode($valor) : (string)$valor,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $acto = \DB::table('actos_notariales')->where('id', $actoId)->first();

        // Construir datos para la minuta usando config de empresa + datos del formulario
        $d = [
            'vendedor_tipo'              => $empresa->minuta_vendedor_tipo ?? 'empresa',
            'vendedor_razon_social'      => $empresa->minuta_vendedor_razon_social ?? $empresa->razon_social,
            'vendedor_ruc'               => $empresa->minuta_vendedor_ruc ?? $empresa->ruc,
            'vendedor_domicilio'         => $empresa->minuta_vendedor_domicilio ?? $empresa->direccion,
            'vendedor_partida_registral' => $empresa->minuta_vendedor_partida ?? '',
            'representante_cargo'        => $empresa->minuta_representante_cargo ?? 'Gerente General',
            'representante_nombre'       => $empresa->minuta_representante_nombre ?? '',
            'representante_dni'          => $empresa->minuta_representante_dni ?? '',
            'representante_estado_civil' => $empresa->minuta_representante_estado_civil ?? 'soltero',
            'representante_profesion'    => $empresa->minuta_representante_profesion ?? '',
            'representante_domicilio'    => $empresa->minuta_representante_domicilio ?? '',
            'comprador_nombre'           => $datos['comprador_nombre'] ?? '',
            'comprador_dni'              => $datos['comprador_dni'] ?? '',
            'comprador_estado_civil'     => $datos['comprador_estado_civil'] ?? 'soltero',
            'comprador_profesion'        => $datos['comprador_profesion'] ?? '',
            'comprador_domicilio'        => $datos['comprador_domicilio'] ?? '',
            'es_bien_futuro'             => !empty($datos['es_bien_futuro']),
            'predio_descripcion'         => $datos['predio_descripcion'] ?? '',
            'predio_partida'             => $datos['predio_partida'] ?? '',
            'ciudad'                     => $empresa->minuta_ciudad ?? 'Huánuco',
            'proyecto_descripcion'       => $datos['proyecto_descripcion'] ?? '',
            'proyecto_municipalidad'     => $datos['proyecto_municipalidad'] ?? '',
            'proyecto_expediente'        => $datos['proyecto_expediente'] ?? '',
            'proyecto_fecha'             => $datos['proyecto_fecha'] ?? '',
            'proyecto_arquitecto'        => $datos['proyecto_arquitecto'] ?? '',
            'plazo_anos'                 => $datos['plazo_anos'] ?? 'tres',
            'lote_descripcion'           => $datos['lote_descripcion'] ?? '',
            'lote_area'                  => $datos['lote_area'] ?? '',
            'lote_area_letras'           => $datos['lote_area_letras'] ?? '',
            'lindero_frente'             => $datos['lindero_frente'] ?? '',
            'medida_frente'              => $datos['medida_frente'] ?? '',
            'lindero_derecha'            => $datos['lindero_derecha'] ?? '',
            'medida_derecha'             => $datos['medida_derecha'] ?? '',
            'lindero_izquierda'          => $datos['lindero_izquierda'] ?? '',
            'medida_izquierda'           => $datos['medida_izquierda'] ?? '',
            'lindero_fondo'              => $datos['lindero_fondo'] ?? '',
            'medida_fondo'               => $datos['medida_fondo'] ?? '',
            'precio_total'               => $datos['precio_total'] ?? $request->monto_cobrar,
            'precio_total_letras'        => $datos['precio_total_letras'] ?? '',
            'forma_pago_detalle'         => $datos['forma_pago_detalle'] ?? '',
            'fecha_minuta'               => $datos['fecha_minuta'] ?? now()->format('d \d\e F \d\e Y'),
        ];

        $html = view('notaria.minuta-compraventa', [
            'acto'    => $acto,
            'empresa' => $empresa,
            'd'       => $d,
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'      => 'Verdana',
                'isRemoteEnabled'  => false,
                'dpi'              => 96,
                'margin_top'       => 113.4,
                'margin_right'     => 85.05,
                'margin_bottom'    => 70.87,
                'margin_left'      => 85.05,
            ]);

        $filename = 'Minuta-CompraVenta-' . $numeroExpediente . '.pdf';
        return $pdf->download($filename);
    }

    public function generarTestimonioCompraventa(Request $request, ActoNotarial $acto)
    {
        $empresa = auth()->user()->empresa;
        $numeroExpediente = $acto->numero_expediente;

        // Cargar datos guardados del acto
        $datosMapa = $acto->datos()->pluck('valor', 'campo')->toArray();
        $datos = array_merge($datosMapa, $request->all());

        // Guardar datos si se solicita
        if (!empty($request->guardar_datos)) {
            foreach ($request->except(['guardar_datos', '_token']) as $campo => $valor) {
                $acto->datos()->updateOrCreate(['campo' => $campo], ['valor' => is_array($valor) ? json_encode($valor) : (string)$valor]);
            }
        }

        $d = [
            'num_instrumento'            => $datos['num_instrumento'] ?? '',
            'num_minuta'                 => $datos['num_minuta'] ?? '',
            'fecha_letras'               => $datos['fecha_letras'] ?? '',
            'fecha_minuta'               => $datos['fecha_minuta'] ?? now()->format('d \d\e F \d\e Y'),
            'fecha_firma'                => $datos['fecha_firma'] ?? '',
            'resolucion_ministerial'     => $datos['resolucion_ministerial'] ?? ($empresa->minuta_resolucion_ministerial ?? ''),
            'fecha_resolucion'           => $datos['fecha_resolucion'] ?? ($empresa->minuta_fecha_resolucion ?? ''),
            'registro_notario'           => $datos['registro_notario'] ?? ($empresa->minuta_registro_notario ?? ''),
            'colegio_notarios'           => $datos['colegio_notarios'] ?? ($empresa->minuta_colegio_notarios ?? 'Huánuco y Pasco'),
            'abogado_nombre'             => $datos['abogado_nombre'] ?? '',
            'abogado_cau'                => $datos['abogado_cau'] ?? '',
            'fojas_inicio'               => $datos['fojas_inicio'] ?? '',
            'fojas_fin'                  => $datos['fojas_fin'] ?? '',
            'papel_serie_inicio'         => $datos['papel_serie_inicio'] ?? '',
            'papel_serie_fin'            => $datos['papel_serie_fin'] ?? '',
            'medios_pago_descripcion'    => $datos['medios_pago_descripcion'] ?? '',
            'medios_pago_tipo'           => $datos['medios_pago_tipo'] ?? 'depósito bancario',
            'alcabala_monto'             => $datos['alcabala_monto'] ?? '',
            'alcabala_fecha'             => $datos['alcabala_fecha'] ?? '',
            'alcabala_recibo'            => $datos['alcabala_recibo'] ?? '',
            'vendedor_tipo'              => $empresa->minuta_vendedor_tipo ?? 'empresa',
            'vendedor_razon_social'      => $empresa->minuta_vendedor_razon_social ?? $empresa->razon_social,
            'vendedor_ruc'               => $empresa->minuta_vendedor_ruc ?? $empresa->ruc,
            'vendedor_domicilio'         => $empresa->minuta_vendedor_domicilio ?? $empresa->direccion ?? '',
            'vendedor_partida_registral' => $empresa->minuta_vendedor_partida ?? '',
            'representante_cargo'        => $empresa->minuta_representante_cargo ?? 'Gerente General',
            'representante_nombre'       => $empresa->minuta_representante_nombre ?? '',
            'representante_dni'          => $empresa->minuta_representante_dni ?? '',
            'representante_estado_civil' => $empresa->minuta_representante_estado_civil ?? 'soltero',
            'representante_profesion'    => $empresa->minuta_representante_profesion ?? '',
            'representante_domicilio'    => $empresa->minuta_representante_domicilio ?? '',
            'comprador_nombre'           => $datos['comprador_nombre'] ?? '',
            'comprador_dni'              => $datos['comprador_dni'] ?? '',
            'comprador_estado_civil'     => $datos['comprador_estado_civil'] ?? 'soltero',
            'comprador_profesion'        => $datos['comprador_profesion'] ?? '',
            'comprador_domicilio'        => $datos['comprador_domicilio'] ?? '',
            'ciudad'                     => $empresa->minuta_ciudad ?? 'Huánuco',
            'predio_descripcion'         => $datos['predio_descripcion'] ?? '',
            'predio_partida'             => $datos['predio_partida'] ?? '',
            'proyecto_descripcion'       => $datos['proyecto_descripcion'] ?? '',
            'proyecto_municipalidad'     => $datos['proyecto_municipalidad'] ?? '',
            'proyecto_expediente'        => $datos['proyecto_expediente'] ?? '',
            'proyecto_fecha'             => $datos['proyecto_fecha'] ?? '',
            'proyecto_arquitecto'        => $datos['proyecto_arquitecto'] ?? '',
            'plazo_anos'                 => $datos['plazo_anos'] ?? 'tres',
            'lote_descripcion'           => $datos['lote_descripcion'] ?? '',
            'lote_area'                  => $datos['lote_area'] ?? '',
            'lote_area_letras'           => $datos['lote_area_letras'] ?? '',
            'lindero_frente'             => $datos['lindero_frente'] ?? '',
            'medida_frente'              => $datos['medida_frente'] ?? '',
            'lindero_derecha'            => $datos['lindero_derecha'] ?? '',
            'medida_derecha'             => $datos['medida_derecha'] ?? '',
            'lindero_izquierda'          => $datos['lindero_izquierda'] ?? '',
            'medida_izquierda'           => $datos['medida_izquierda'] ?? '',
            'lindero_fondo'              => $datos['lindero_fondo'] ?? '',
            'medida_fondo'               => $datos['medida_fondo'] ?? '',
            'precio_total'               => $datos['precio_total'] ?? $acto->monto_cobrar,
            'precio_total_letras'        => $datos['precio_total_letras'] ?? '',
            'forma_pago_detalle'         => $datos['forma_pago_detalle'] ?? '',
        ];

        $html = view('notaria.testimonio-compraventa', [
            'acto'    => $acto,
            'empresa' => $empresa,
            'd'       => $d,
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'      => 'Verdana',
                'isRemoteEnabled'  => false,
                'dpi'              => 96,
                'margin_top'       => 113.4,
                'margin_right'     => 85.05,
                'margin_bottom'    => 70.87,
                'margin_left'      => 85.05,
            ]);

        $filename = 'Testimonio-CompraVenta-' . $numeroExpediente . '.pdf';
        return $pdf->download($filename);
    }

    public function generarParteCompraventa(Request $request, ActoNotarial $acto)
    {
        $empresa = auth()->user()->empresa;
        $datosMapa = $acto->datos()->pluck('valor', 'campo')->toArray();
        $datos = array_merge($datosMapa, $request->all());

        if (!empty($request->guardar_datos)) {
            foreach ($request->except(['guardar_datos', '_token']) as $campo => $valor) {
                $acto->datos()->updateOrCreate(['campo' => $campo], ['valor' => is_array($valor) ? json_encode($valor) : (string)$valor]);
            }
        }

        $d = [
            'num_instrumento'     => $datos['num_instrumento'] ?? '',
            'num_minuta'          => $datos['num_minuta'] ?? '',
            'fecha_letras'        => $datos['fecha_letras'] ?? '',
            'fecha_minuta'        => $datos['fecha_minuta'] ?? now()->format('d \d\e F \d\e Y'),
            'fecha_firma'         => $datos['fecha_firma'] ?? '',
            'ciudad'              => $empresa->minuta_ciudad ?? 'Huánuco',
            'vendedor_nombre'     => $datos['vendedor_nombre'] ?? '',
            'vendedor_dni'        => $datos['vendedor_dni'] ?? '',
            'vendedor_estado_civil' => $datos['vendedor_estado_civil'] ?? '',
            'vendedor_profesion'  => $datos['vendedor_profesion'] ?? '',
            'vendedor_domicilio'  => $datos['vendedor_domicilio'] ?? '',
            'comprador_nombre'    => $datos['comprador_nombre'] ?? '',
            'comprador_dni'       => $datos['comprador_dni'] ?? '',
            'comprador_estado_civil' => $datos['comprador_estado_civil'] ?? '',
            'comprador_profesion' => $datos['comprador_profesion'] ?? '',
            'comprador_domicilio' => $datos['comprador_domicilio'] ?? '',
            'comprador2_nombre'   => $datos['comprador2_nombre'] ?? '',
            'comprador2_dni'      => $datos['comprador2_dni'] ?? '',
            'comprador2_estado_civil' => $datos['comprador2_estado_civil'] ?? '',
            'comprador2_profesion' => $datos['comprador2_profesion'] ?? '',
            'comprador2_domicilio' => $datos['comprador2_domicilio'] ?? '',
            'predio_descripcion'  => $datos['predio_descripcion'] ?? '',
            'predio_partida'      => $datos['predio_partida'] ?? '',
            'antecedente_registral' => $datos['antecedente_registral'] ?? '',
            'precio_total'        => $datos['precio_total'] ?? '',
            'precio_total_letras' => $datos['precio_total_letras'] ?? '',
            'forma_pago_detalle'  => $datos['forma_pago_detalle'] ?? '',
            'medios_pago_tipo'    => $datos['medios_pago_tipo'] ?? 'DEPÓSITO EN CUENTA',
            'anotacion'           => $datos['anotacion'] ?? '',
            'abogado_nombre'      => $datos['abogado_nombre'] ?? '',
            'abogado_cau'         => $datos['abogado_cau'] ?? '',
            'fojas_inicio'        => $datos['fojas_inicio'] ?? '',
            'fojas_fin'           => $datos['fojas_fin'] ?? '',
            'papel_serie_inicio'  => $datos['papel_serie_inicio'] ?? '',
            'papel_serie_fin'     => $datos['papel_serie_fin'] ?? '',
        ];

        $html = view('notaria.parte-compraventa', compact('acto', 'empresa', 'd'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'     => 'Verdana',
                'isRemoteEnabled' => false,
                'dpi'             => 96,
            ]);

        return $pdf->download('Parte-CompraVenta-' . $acto->numero_expediente . '.pdf');
    }
}