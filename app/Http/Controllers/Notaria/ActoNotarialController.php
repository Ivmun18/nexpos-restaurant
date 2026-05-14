<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoSeguimiento;
use App\Models\ActoDato;
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
        $hasta     = $request->get('hasta', now()->toDateString());

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

        $clientes = Cliente::where('empresa_id', $empresaId)->orderBy('razon_social')->get(['id', 'razon_social as nombre', 'numero_documento']);

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
        $acto->load(['cliente', 'usuario', 'documentos.usuario', 'seguimientos.usuario', 'datos']);
        $datosMapa = $acto->datos->pluck('valor', 'campo');
        return Inertia::render('Notaria/Actos/Show', ['acto' => $acto, 'datos' => $datosMapa]);
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

        $sesion = \App\Models\SesionCaja::where('estado', 'abierta')->first();
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
}
