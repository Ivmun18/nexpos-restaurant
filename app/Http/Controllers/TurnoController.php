<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\CajaRestaurante;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TurnoController extends Controller
{
    public function index(): Response
    {
        $turnoActivo = Turno::where('user_id', auth()->id())
            ->where('estado', 'abierto')
            ->first();

        $turnos = Turno::with('user')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return Inertia::render('Turnos/Index', [
            'turnoActivo' => $turnoActivo,
            'turnos'      => $turnos,
        ]);
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'tipo'   => 'required|in:mañana,tarde,noche,personalizado',
            'nombre' => 'nullable|string|max:100',
            'notas'  => 'nullable|string',
        ]);

        // Verificar que no tenga turno abierto
        $turnoExistente = Turno::where('user_id', auth()->id())
            ->where('estado', 'abierto')
            ->first();

        if ($turnoExistente) {
            return redirect()->back()->with('error', 'Ya tienes un turno abierto.');
        }

        Turno::create([
            'user_id'  => auth()->id(),
            'tipo'     => $request->tipo,
            'nombre'   => $request->nombre,
            'notas'    => $request->notas,
            'estado'   => 'abierto',
            'apertura' => now(),
        ]);

        return redirect()->back()->with('success', 'Turno abierto correctamente.');
    }

    public function cerrar(Turno $turno)
    {
        // Calcular totales del turno
        $totalVentas = CajaRestaurante::where('user_id', auth()->id())
            ->whereBetween('created_at', [$turno->apertura, now()])
            ->sum('total');

        $totalMesas = CajaRestaurante::where('user_id', auth()->id())
            ->whereBetween('created_at', [$turno->apertura, now()])
            ->count();

        $turno->update([
            'estado'       => 'cerrado',
            'cierre'       => now(),
            'total_ventas' => $totalVentas,
            'total_mesas'  => $totalMesas,
        ]);

        return redirect()->back()->with('success', 'Turno cerrado. Total: S/ ' . number_format($totalVentas, 2));
    }

    public function show(Turno $turno): Response
    {
        $turno->load('user');

        $cobros = CajaRestaurante::with('mesa')
            ->where('user_id', $turno->user_id)
            ->whereBetween('created_at', [
                $turno->apertura,
                $turno->cierre ?? now()
            ])
            ->get();

        return Inertia::render('Turnos/Show', [
            'turno'  => $turno,
            'cobros' => $cobros,
        ]);
    }
}