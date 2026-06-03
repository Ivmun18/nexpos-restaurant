<?php
namespace App\Http\Controllers\Hotel;
use App\Http\Controllers\Controller;
use App\Models\HotelTipoHabitacion;
use App\Models\HotelHabitacion;
use App\Models\HotelHuesped;
use App\Models\HotelReserva;
use App\Models\HotelPago;
use App\Models\HotelHousekeeping;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HotelController extends Controller
{
    // ── DASHBOARD ──
    public function dashboard()
    {
        $empresaId = auth()->user()->empresa_id;
        $hoy = Carbon::today();

        $totalHabitaciones  = HotelHabitacion::where('empresa_id', $empresaId)->count();
        $disponibles        = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'disponible')->count();
        $ocupadas           = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'ocupada')->count();
        $limpieza           = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'limpieza')->count();
        $checkinsHoy        = HotelReserva::where('empresa_id', $empresaId)->whereDate('fecha_checkin', $hoy)->count();
        $checkoutsHoy       = HotelReserva::where('empresa_id', $empresaId)->whereDate('fecha_checkout_previsto', $hoy)->where('estado', 'checkin')->count();
        $ingresosMes        = HotelReserva::where('empresa_id', $empresaId)->whereMonth('created_at', $hoy->month)->sum('monto_pagado');

        $habitaciones = HotelHabitacion::with('tipo','reservaActual.huesped')
            ->where('empresa_id', $empresaId)
            ->orderBy('piso')->orderBy('numero')
            ->get();

        return Inertia::render('Hotel/Dashboard', compact(
            'totalHabitaciones','disponibles','ocupadas','limpieza',
            'checkinsHoy','checkoutsHoy','ingresosMes','habitaciones'
        ));
    }

    // ── HABITACIONES ──
    public function habitaciones()
    {
        $empresaId = auth()->user()->empresa_id;
        $habitaciones = HotelHabitacion::with('tipo')
            ->where('empresa_id', $empresaId)
            ->orderBy('piso')->orderBy('numero')->get();
        $tipos = HotelTipoHabitacion::where('empresa_id', $empresaId)->where('activo', true)->get();
        return Inertia::render('Hotel/Habitaciones', compact('habitaciones','tipos'));
    }

    public function storeHabitacion(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelHabitacion::create([...$request->only(['tipo_id','numero','piso','estado','observaciones']), 'empresa_id' => $empresaId]);
        return back()->with('success', 'Habitación creada');
    }

    public function updateHabitacion(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelHabitacion::where('id', $id)->where('empresa_id', $empresaId)->update($request->only(['tipo_id','numero','piso','estado','observaciones']));
        return back()->with('success', 'Habitación actualizada');
    }

    public function destroyHabitacion($id)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelHabitacion::where('id', $id)->where('empresa_id', $empresaId)->delete();
        return back()->with('success', 'Habitación eliminada');
    }

    // ── TIPOS DE HABITACIÓN ──
    public function tipos()
    {
        $empresaId = auth()->user()->empresa_id;
        $tipos = HotelTipoHabitacion::where('empresa_id', $empresaId)->get();
        return Inertia::render('Hotel/Tipos', compact('tipos'));
    }

    public function storeTipo(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'precio_noche' => 'required|numeric|min:0',
            'capacidad'    => 'required|integer|min:1',
        ]);
        HotelTipoHabitacion::create([...$request->only(['nombre','descripcion','precio_noche','capacidad','comodidades','activo']), 'empresa_id' => $empresaId]);
        return back()->with('success', 'Tipo creado');
    }

    public function updateTipo(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelTipoHabitacion::where('id', $id)->where('empresa_id', $empresaId)->update($request->only(['nombre','descripcion','precio_noche','capacidad','comodidades','activo']));
        return back()->with('success', 'Tipo actualizado');
    }

    // ── RECEPCIÓN / RESERVAS ──
    public function recepcion()
    {
        $empresaId = auth()->user()->empresa_id;
        $reservas = HotelReserva::with('habitacion.tipo','huesped')
            ->where('empresa_id', $empresaId)
            ->whereIn('estado', ['reservado','checkin'])
            ->orderBy('fecha_checkin','desc')->get();
        $habitacionesDisponibles = HotelHabitacion::with('tipo')
            ->where('empresa_id', $empresaId)
            ->where('estado', 'disponible')->get();
        $huespedes = HotelHuesped::where('empresa_id', $empresaId)->orderBy('nombre_completo')->get();
        $todasHabitaciones = HotelHabitacion::with('tipo')
            ->where('empresa_id', $empresaId)
            ->orderBy('numero')->get();
        return Inertia::render('Hotel/Recepcion', compact('reservas','habitacionesDisponibles','huespedes','todasHabitaciones'));
    }

    public function checkin(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $habitacion = HotelHabitacion::findOrFail($request->habitacion_id);
        $noches = max(1, Carbon::parse($request->fecha_checkin)->startOfDay()->diffInDays(Carbon::parse($request->fecha_checkout)->startOfDay()));
        $total  = $habitacion->tipo->precio_noche * $noches;

        // Crear o encontrar huésped
        $huesped = HotelHuesped::firstOrCreate(
            ['empresa_id' => $empresaId, 'numero_documento' => $request->numero_documento],
            ['tipo_documento' => $request->tipo_documento, 'nombre_completo' => $request->nombre_completo,
             'email' => $request->email, 'telefono' => $request->telefono, 'nacionalidad' => $request->nacionalidad ?? 'Peruana']
        );

        $codigo = 'RES-' . date('Y') . '-' . str_pad(HotelReserva::where('empresa_id', $empresaId)->count() + 1, 4, '0', STR_PAD_LEFT);

        $reserva = HotelReserva::create([
            'empresa_id'             => $empresaId,
            'habitacion_id'          => $habitacion->id,
            'huesped_id'             => $huesped->id,
            'usuario_id'             => auth()->id(),
            'codigo'                 => $codigo,
            'fecha_checkin'          => $request->fecha_checkin,
            'fecha_checkout_previsto'=> $request->fecha_checkout,
            'num_huespedes'          => $request->num_huespedes ?? 1,
            'precio_noche'           => $habitacion->tipo->precio_noche,
            'num_noches'             => $noches,
            'total'                  => $total,
            'monto_pagado'           => 0,
            'estado'                 => 'checkin',
            'estado_pago'            => 'pendiente',
            'observaciones'          => $request->observaciones,
        ]);

        $habitacion->update(['estado' => 'ocupada']);

        return response()->json(['success' => true, 'reserva' => $reserva, 'mensaje' => 'Check-in realizado ✅']);
    }

    public function checkout(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $reserva = HotelReserva::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();

        // Registrar pago si hay monto
        if ($request->monto > 0) {
            HotelPago::create([
                'reserva_id'  => $reserva->id,
                'usuario_id'  => auth()->id(),
                'monto'       => $request->monto,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'referencia'  => $request->referencia,
            ]);
            $reserva->increment('monto_pagado', $request->monto);
            $reserva->refresh();
        }

        $estadoPago = $reserva->monto_pagado >= $reserva->total ? 'pagado' : 'parcial';

        $reserva->update([
            'estado'           => 'checkout',
            'estado_pago'      => $estadoPago,
            'fecha_checkout_real' => now(),
        ]);

        // Marcar habitación para limpieza
        $reserva->habitacion->update(['estado' => 'limpieza']);
        HotelHousekeeping::create([
            'empresa_id'    => $empresaId,
            'habitacion_id' => $reserva->habitacion_id,
            'usuario_id'    => auth()->id(),
            'estado'        => 'pendiente',
        ]);

        $reserva->load('habitacion.tipo', 'huesped', 'pagos');
        return response()->json([
            'success' => true,
            'mensaje' => 'Check-out realizado ✅',
            'ticket'  => [
                'codigo'          => $reserva->codigo,
                'huesped'         => $reserva->huesped->nombre_completo,
                'documento'       => $reserva->huesped->numero_documento,
                'habitacion'      => 'Hab. ' . $reserva->habitacion->numero . ' — ' . $reserva->habitacion->tipo->nombre,
                'fecha_checkin'   => $reserva->fecha_checkin,
                'fecha_checkout'  => $reserva->fecha_checkout_real,
                'noches'          => $reserva->noches,
                'precio_noche'    => $reserva->precio_noche,
                'total'           => $reserva->total,
                'monto_pagado'    => $reserva->monto_pagado,
                'estado_pago'     => $reserva->estado_pago,
                'metodo_pago'     => $request->metodo_pago ?? 'efectivo',
            ]
        ]);
    }

    // ── HOUSEKEEPING ──
    public function housekeeping()
    {
        $empresaId = auth()->user()->empresa_id;
        $tareas = HotelHousekeeping::with('habitacion.tipo','usuario')
            ->where('empresa_id', $empresaId)
            ->whereIn('estado', ['pendiente','en_proceso'])
            ->orderBy('created_at','desc')->get();
        $habitaciones = HotelHabitacion::where('empresa_id', $empresaId)
            ->whereIn('estado', ['limpieza','mantenimiento'])->with('tipo')->get();
        return Inertia::render('Hotel/Housekeeping', compact('tareas','habitaciones'));
    }

    public function actualizarHousekeeping(Request $request, $id)
    {
        $tarea = HotelHousekeeping::findOrFail($id);
        $tarea->update([
            'estado'        => $request->estado,
            'observaciones' => $request->observaciones,
            'completado_at' => $request->estado === 'completado' ? now() : null,
        ]);
        if ($request->estado === 'completado') {
            $tarea->habitacion->update(['estado' => 'disponible']);
        }
        return redirect()->back()->with('success', 'Tarea actualizada ✅');
    }

    // ── REPORTES ──
    public function reportes(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->format('Y-m-d'));

        $reservas = HotelReserva::with('habitacion.tipo','huesped')
            ->where('empresa_id', $empresaId)
            ->whereBetween('fecha_checkin', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('fecha_checkin','desc')->get();

        $totalIngresos = $reservas->sum('monto_pagado');
        $totalReservas = $reservas->count();
        $ocupacionPromedio = HotelHabitacion::where('empresa_id', $empresaId)->count() > 0
            ? round($reservas->where('estado','checkin')->count() / HotelHabitacion::where('empresa_id', $empresaId)->count() * 100, 1)
            : 0;

        return Inertia::render('Hotel/Reportes', compact('reservas','totalIngresos','totalReservas','ocupacionPromedio','desde','hasta'));
    }
}
