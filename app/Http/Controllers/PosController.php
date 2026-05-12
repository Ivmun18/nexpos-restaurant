<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\MenuCategoria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    // Abre el POS de una mesa
    public function index(Mesa $mesa): Response
    {
        // Verificar caja abierta
        $empresaId = auth()->user()->empresa_id;
        $caja = \App\Models\Caja::where('empresa_id', $empresaId)->where('activo', true)->first();
        if (!$caja) {
            $caja = \App\Models\Caja::create(['empresa_id' => $empresaId, 'codigo' => 'CAJA01', 'nombre' => 'Caja Principal', 'activo' => true]);
        }
        $sesionAbierta = \App\Models\SesionCaja::where('caja_id', $caja->id)
            ->where('estado', 'abierta')
            ->first();
        if (!$sesionAbierta) {
            return redirect()->route('caja.index')->with('warning', '⚠️ Debes abrir la caja antes de atender mesas.');
        }

        // Cargar carta completa
       $categorias = MenuCategoria::with('productosActivos')
    ->where('activo', true)
    ->orderBy('orden')
    ->get();
    
        // Pedidos abiertos de esta mesa
        $pedidosAbiertos = Pedido::with('detalles')
        ->where('mesa_id', $mesa->id)
        ->whereIn('estado', ['abierto', 'enviado', 'listo'])
         ->orderBy('numero_ronda')
         ->get();

        // Calcular siguiente número de ronda
        $ultimaRonda = Pedido::where('mesa_id', $mesa->id)
            ->whereIn('estado', ['abierto', 'enviado'])
            ->max('numero_ronda') ?? 0;

        return Inertia::render('Pos/Index', [
            'mesa'          => $mesa,
            'categorias'    => $categorias,
            'pedidosAbiertos' => $pedidosAbiertos,
            'siguienteRonda'  => $ultimaRonda + 1,
        ]);
    }

    public function store(Request $request, Mesa $mesa)
{
       \Log::info('POS store llamado', [
        'items' => $request->items,
        'notas' => $request->notas,
    ]);
    $validated = $request->validate([
        'items'                    => 'required|array|min:1',
        'items.*.menu_producto_id' => 'required|exists:menu_productos,id',
        'items.*.nombre_producto'  => 'required|string',
        'items.*.cantidad'         => 'required|integer|min:1',
        'items.*.precio_unitario'  => 'required|numeric|min:0',
        'items.*.notas'            => 'nullable|string',
        'notas'                    => 'nullable|string',
    ]);

    $ultimaRonda = Pedido::where('mesa_id', $mesa->id)
         ->whereIn('estado', ['abierto', 'enviado', 'listo'])
        ->max('numero_ronda') ?? 0;

    $pedido = Pedido::create([
        'mesa_id'      => $mesa->id,
        'user_id'      => auth()->id(),
        'estado'       => 'enviado',
        'numero_ronda' => $ultimaRonda + 1,
        'notas'        => $request->notas,
        'total'        => 0,
    ]);

    foreach ($validated['items'] as $item) {
        $subtotal = $item['cantidad'] * $item['precio_unitario'];
        PedidoDetalle::create([
            'pedido_id'        => $pedido->id,
            'menu_producto_id' => $item['menu_producto_id'],
            'nombre_producto'  => $item['nombre_producto'],
            'cantidad'         => $item['cantidad'],
            'precio_unitario'  => $item['precio_unitario'],
            'subtotal'         => $subtotal,
            'notas'            => $item['notas'] ?? null,
            'estado'           => 'pendiente',
        ]);
    }

    $pedido->recalcularTotal();
    $mesa->update(['estado' => 'ocupada']);

    return redirect()->route('mesas.index')
        ->with('success', "Pedido enviado a cocina. Mesa {$mesa->numero}");
}


    // Cerrar todos los pedidos de la mesa
    public function cerrar(Mesa $mesa)
    {
        Pedido::where('mesa_id', $mesa->id)
            ->whereIn('estado', ['abierto', 'enviado', 'listo'])
            ->update(['estado' => 'cerrado']);

        $mesa->update(['estado' => 'libre']);

        return redirect()->route('mesas.index')
            ->with('success', "Mesa {$mesa->numero} cerrada.");
    }
}