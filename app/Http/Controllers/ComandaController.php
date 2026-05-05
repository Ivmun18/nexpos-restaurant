<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComandaController extends Controller
{
    /**
     * Vista principal de comandas (Kanban)
     */
    public function index(Request $request): Response
    {
        $filtroMesa = $request->input('mesa');
        $filtroMozo = $request->input('mozo');

        // Comandas pendientes (estado: enviado)
        $pendientes = $this->getComandas('enviado', $filtroMesa, $filtroMozo);

        // Comandas en preparación
        $enPreparacion = $this->getComandas('en_preparacion', $filtroMesa, $filtroMozo);

        // Comandas listas
        $listas = $this->getComandas('listo', $filtroMesa, $filtroMozo);

        // Lista de mozos para filtro
        $mozos = \App\Models\User::where('rol', 'mozo')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Comandas/Index', [
            'pendientes' => $pendientes,
            'en_preparacion' => $enPreparacion,
            'listas' => $listas,
            'mozos' => $mozos,
            'filtros' => [
                'mesa' => $filtroMesa,
                'mozo' => $filtroMozo,
            ],
        ]);
    }

    /**
     * Obtener comandas por estado
     */
    private function getComandas(string $estado, $filtroMesa = null, $filtroMozo = null)
    {
        $query = Pedido::with(['mesa', 'user', 'detalles'])
            ->where('estado', $estado)
            ->orderBy('created_at', 'asc');

        if ($filtroMesa) {
            $query->where('mesa_id', $filtroMesa);
        }

        if ($filtroMozo) {
            $query->where('user_id', $filtroMozo);
        }

        return $query->get()->map(function ($pedido) {
            $minutosTranscurridos = now()->diffInMinutes($pedido->created_at);
            
            return [
                'id' => $pedido->id,
                'mesa' => [
                    'id' => $pedido->mesa->id,
                    'numero' => $pedido->mesa->numero,
                ],
                'mozo' => $pedido->user->name ?? 'Sin asignar',
                'estado' => $pedido->estado,
                'numero_ronda' => $pedido->numero_ronda,
                'detalles' => $pedido->detalles->map(function ($detalle) {
                    return [
                        'id' => $detalle->id,
                        'producto' => $detalle->nombre_producto,
                        'cantidad' => $detalle->cantidad,
                        'notas' => $detalle->notas,
                        'estado' => $detalle->estado,
                    ];
                }),
                'tiempo_transcurrido' => $minutosTranscurridos,
                'prioridad' => $minutosTranscurridos > 15 ? 'alta' : ($minutosTranscurridos > 8 ? 'media' : 'normal'),
                'created_at' => $pedido->created_at->format('H:i'),
            ];
        });
    }

    /**
     * Cambiar estado de un pedido completo
     */
    public function cambiarEstado(Pedido $pedido, Request $request)
    {
        $request->validate([
            'estado' => 'required|in:enviado,en_preparacion,listo',
        ]);

        $pedido->update([
            'estado' => $request->estado,
        ]);

        return redirect()->back()->with('success', 'Estado actualizado');
    }

    /**
     * Marcar un plato individual como listo
     */
    public function marcarPlatoListo(PedidoDetalle $detalle)
    {
        $detalle->update(['estado' => 'listo']);

        // Si todos los platos del pedido están listos, marcar el pedido como listo
        $pedido = $detalle->pedido;
        $todosListos = $pedido->detalles()->where('estado', '!=', 'listo')->count() === 0;

        if ($todosListos) {
            $pedido->update(['estado' => 'listo']);
        }

        return response()->json([
            'success' => true,
            'detalle_estado' => 'listo',
            'pedido_estado' => $pedido->fresh()->estado,
        ]);
    }

    /**
     * Marcar un plato individual como en preparación
     */
    public function marcarPlatoEnPreparacion(PedidoDetalle $detalle)
    {
        $detalle->update(['estado' => 'en_preparacion']);

        // Actualizar el pedido a en_preparacion si no lo está ya
        $pedido = $detalle->pedido;
        if ($pedido->estado === 'enviado') {
            $pedido->update(['estado' => 'en_preparacion']);
        }

        return response()->json([
            'success' => true,
            'detalle_estado' => 'en_preparacion',
            'pedido_estado' => $pedido->fresh()->estado,
        ]);
    }

    /**
     * API para polling (actualización automática)
     */
    public function polling(Request $request)
    {
        $filtroMesa = $request->input('mesa');
        $filtroMozo = $request->input('mozo');

        return response()->json([
            'pendientes' => $this->getComandas('enviado', $filtroMesa, $filtroMozo),
            'en_preparacion' => $this->getComandas('en_preparacion', $filtroMesa, $filtroMozo),
            'listas' => $this->getComandas('listo', $filtroMesa, $filtroMozo),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
