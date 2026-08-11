<?php
namespace App\Http\Controllers\Hotel;
use App\Http\Controllers\Controller;
use App\Models\HotelTipoHabitacion;
use App\Models\HotelHabitacion;
use App\Models\HotelHuesped;
use App\Models\HotelReserva;
use App\Models\HotelPago;
use App\Models\HotelHousekeeping;
use App\Models\HotelTarifaTemporada;
use App\Models\HotelProducto;
use App\Models\HotelCargo;
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

        // Estado habitaciones
        $totalHabitaciones  = HotelHabitacion::where('empresa_id', $empresaId)->count();
        $disponibles        = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'disponible')->count();
        $ocupadas           = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'ocupada')->count();
        $limpieza           = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'limpieza')->count();
        $mantenimiento      = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'mantenimiento')->count();
        $ocupacionPct       = $totalHabitaciones > 0 ? round(($ocupadas / $totalHabitaciones) * 100) : 0;

        // Actividad hoy
        $checkinsHoy    = HotelReserva::where('empresa_id', $empresaId)->whereDate('fecha_checkin', $hoy)->count();
        $checkoutsHoy   = HotelReserva::where('empresa_id', $empresaId)->whereDate('fecha_checkout_previsto', $hoy)->where('estado', 'checkin')->count();
        $ingresosHoy    = HotelPago::whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId))->whereDate('created_at', $hoy)->sum('monto');

        // Mes actual
        $ingresosMes    = HotelPago::whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId))->whereMonth('created_at', $hoy->month)->whereYear('created_at', $hoy->year)->sum('monto');

        // Saldos pendientes (reservas activas con deuda)
        $saldoPendiente = HotelReserva::where('empresa_id', $empresaId)
            ->where('estado', 'checkin')
            ->whereRaw('monto_pagado < total')
            ->selectRaw('SUM(total - monto_pagado) as saldo')
            ->value('saldo') ?? 0;

        $reservasPendientesPago = HotelReserva::where('empresa_id', $empresaId)
            ->where('estado', 'checkin')
            ->whereRaw('monto_pagado < total')
            ->count();

        // Próximos checkouts (hoy y mañana)
        $proximosCheckouts = HotelReserva::with('habitacion.tipo','huesped')
            ->where('empresa_id', $empresaId)
            ->where('estado', 'checkin')
            ->whereDate('fecha_checkout_previsto', '<=', $hoy->copy()->addDay())
            ->orderBy('fecha_checkout_previsto')
            ->get();

        // Próximos checkins (hoy y mañana)
        $proximosCheckins = HotelReserva::with('habitacion.tipo','huesped')
            ->where('empresa_id', $empresaId)
            ->where('estado', 'reservado')
            ->whereDate('fecha_checkin', '<=', $hoy->copy()->addDay())
            ->orderBy('fecha_checkin')
            ->get();

        // Housekeeping pendiente
        $housekeepingPendiente = HotelHousekeeping::where('empresa_id', $empresaId)
            ->whereIn('estado', ['pendiente','en_proceso'])
            ->count();

        $habitaciones = HotelHabitacion::with('tipo','reservaActual.huesped')
            ->where('empresa_id', $empresaId)
            ->orderBy('piso')->orderBy('numero')
            ->get();

        return Inertia::render('Hotel/Dashboard', compact(
            'totalHabitaciones','disponibles','ocupadas','limpieza','mantenimiento','ocupacionPct',
            'checkinsHoy','checkoutsHoy','ingresosHoy','ingresosMes',
            'saldoPendiente','reservasPendientesPago',
            'proximosCheckouts','proximosCheckins',
            'housekeepingPendiente','habitaciones'
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
        $reservas = HotelReserva::with('habitacion.tipo','huesped','pagos')
            ->where('empresa_id', $empresaId)
            ->whereIn('estado', ['reservado','checkin'])
            ->orderBy('fecha_checkin','desc')->get();
        $habitacionesDisponibles = HotelHabitacion::with('tipo')
            ->where('empresa_id', $empresaId)
            ->where('estado', 'disponible')->get();
        $huespedes = HotelHuesped::where('empresa_id', $empresaId)->orderBy('nombre_completo')->get();
        $todasHabitaciones = HotelHabitacion::with(['tipo', 'reservasFuturas' => function($q) {
                $q->where('estado', 'reservado')
                  ->where('fecha_checkin', '>', now())
                  ->orderBy('fecha_checkin')
                  ->with('huesped');
            }])
            ->where('empresa_id', $empresaId)
            ->orderBy('numero')->get();
        $productos = HotelProducto::where('empresa_id', $empresaId)->where('activo', true)->orderBy('categoria')->orderBy('nombre')->get();
        $cargos = HotelCargo::with('producto','reserva')
            ->where('empresa_id', $empresaId)
            ->whereHas('reserva', fn($q) => $q->where('estado', 'checkin'))
            ->orderByDesc('created_at')->get();
        return Inertia::render('Hotel/Recepcion', compact('reservas','habitacionesDisponibles','huespedes','todasHabitaciones','productos','cargos'));
    }

    public function checkin(Request $request)
    {
        $empresaId  = auth()->user()->empresa_id;
        $habitacion = HotelHabitacion::findOrFail($request->habitacion_id);
        $noches     = max(1, Carbon::parse($request->fecha_checkin)->startOfDay()->diffInDays(Carbon::parse($request->fecha_checkout)->startOfDay()));
        // Tarifa de temporada
        $tarifaTemp = HotelTarifaTemporada::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where('fecha_inicio', '<=', Carbon::parse($request->fecha_checkin)->toDateString())
            ->where('fecha_fin', '>=', Carbon::parse($request->fecha_checkin)->toDateString())
            ->where(function($q) use ($habitacion) {
                $q->whereNull('tipo_id')->orWhere('tipo_id', $habitacion->tipo_id);
            })
            ->orderByDesc('tipo_id')
            ->first();
        $precioNoche = $tarifaTemp ? $tarifaTemp->precio_noche : $habitacion->tipo->precio_noche;
        $total      = $precioNoche * $noches;

        // Verificar si hay reservas futuras que se cruzan con las fechas solicitadas
        $checkinSolicitado  = Carbon::parse($request->fecha_checkin)->startOfDay();
        $checkoutSolicitado = Carbon::parse($request->fecha_checkout)->startOfDay();

        $conflicto = HotelReserva::where('habitacion_id', $habitacion->id)
            ->where('estado', 'reservado')
            ->where(function($q) use ($checkinSolicitado, $checkoutSolicitado) {
                $q->whereBetween('fecha_checkin', [$checkinSolicitado, $checkoutSolicitado])
                  ->orWhereBetween('fecha_checkout_previsto', [$checkinSolicitado, $checkoutSolicitado])
                  ->orWhere(function($q2) use ($checkinSolicitado, $checkoutSolicitado) {
                      $q2->where('fecha_checkin', '<=', $checkinSolicitado)
                         ->where('fecha_checkout_previsto', '>=', $checkoutSolicitado);
                  });
            })->first();

        if ($conflicto) {
            $fechaReserva = Carbon::parse($conflicto->fecha_checkin)->format('d/m/Y');
            return response()->json([
                'success' => false,
                'mensaje' => "⚠️ La habitación {$habitacion->numero} está reservada para el {$fechaReserva}. No se puede hacer check-in en esas fechas."
            ], 422);
        }

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
            'precio_noche'           => $precioNoche,
            'num_noches'             => $noches,
            'total'                  => $total,
            'monto_pagado'           => 0,
            'estado'                 => 'checkin',
            'estado_pago'            => 'pendiente',
            'observaciones'          => $request->observaciones,
        ]);

        $habitacion->update(['estado' => 'ocupada']);

        return response()->json(['success' => true, 'reserva' => $reserva, 'codigo' => $codigo, 'mensaje' => 'Check-in realizado ✅']);
    }


    // ── TICKET CHECK-IN ──
    public function ticketCheckin($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $reserva = HotelReserva::with('habitacion.tipo', 'huesped')
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        $empresa = auth()->user()->empresa;

        $logoBase64 = '';
        if ($empresa->logo) {
            $logoPath = public_path('storage/' . $empresa->logo);
            if (file_exists($logoPath)) {
                $ext = pathinfo($logoPath, PATHINFO_EXTENSION);
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        return response()->json([
            'empresa' => [
                'nombre'    => $empresa->nombre_comercial ?? $empresa->razon_social,
                'ruc'       => $empresa->ruc,
                'direccion' => $empresa->direccion,
                'telefono'  => $empresa->telefono,
                'logo'      => $logoBase64,
            ],
            'reserva' => [
                'codigo'             => $reserva->codigo,
                'fecha_checkin'      => $reserva->fecha_checkin,
                'fecha_checkout'     => $reserva->fecha_checkout_previsto,
                'num_noches'         => $reserva->num_noches,
                'num_huespedes'      => $reserva->num_huespedes,
                'precio_noche'       => $reserva->precio_noche,
                'total'              => $reserva->total,
                'monto_pagado'       => $reserva->monto_pagado,
                'saldo'              => $reserva->total - $reserva->monto_pagado,
                'observaciones'      => $reserva->observaciones,
            ],
            'habitacion' => [
                'numero' => $reserva->habitacion->numero,
                'piso'   => $reserva->habitacion->piso,
                'tipo'   => $reserva->habitacion->tipo->nombre,
            ],
            'huesped' => [
                'nombre'          => $reserva->huesped->nombre_completo,
                'tipo_documento'  => $reserva->huesped->tipo_documento,
                'numero_documento'=> $reserva->huesped->numero_documento,
                'telefono'        => $reserva->huesped->telefono,
                'nacionalidad'    => $reserva->huesped->nacionalidad,
            ],
        ]);
    }

    // ── RESERVA ANTICIPADA ──
    public function reservar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $habitacion = HotelHabitacion::findOrFail($request->habitacion_id);
        $noches = max(1, Carbon::parse($request->fecha_checkin)->startOfDay()->diffInDays(Carbon::parse($request->fecha_checkout)->startOfDay()));
        $tarifaTemp2 = HotelTarifaTemporada::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where('fecha_inicio', '<=', Carbon::parse($request->fecha_checkin)->toDateString())
            ->where('fecha_fin', '>=', Carbon::parse($request->fecha_checkin)->toDateString())
            ->where(function($q) use ($habitacion) {
                $q->whereNull('tipo_id')->orWhere('tipo_id', $habitacion->tipo_id);
            })
            ->orderByDesc('tipo_id')
            ->first();
        $precioNoche2 = $tarifaTemp2 ? $tarifaTemp2->precio_noche : $habitacion->tipo->precio_noche;
        $total  = $precioNoche2 * $noches;

        $huesped = HotelHuesped::firstOrCreate(
            ['empresa_id' => $empresaId, 'numero_documento' => $request->numero_documento],
            ['tipo_documento' => $request->tipo_documento ?? 'DNI', 'nombre_completo' => $request->nombre_completo,
             'email' => $request->email, 'telefono' => $request->telefono, 'nacionalidad' => $request->nacionalidad ?? 'Peruana']
        );

        $codigo = 'RES-' . date('Y') . '-' . str_pad(HotelReserva::where('empresa_id', $empresaId)->count() + 1, 4, '0', STR_PAD_LEFT);

        // Verificar conflicto de fechas
        $checkinSol  = Carbon::parse($request->fecha_checkin)->startOfDay();
        $checkoutSol = Carbon::parse($request->fecha_checkout)->startOfDay();

        $conflicto = HotelReserva::where('habitacion_id', $habitacion->id)
            ->whereIn('estado', ['reservado', 'checkin'])
            ->where(function($q) use ($checkinSol, $checkoutSol) {
                $q->whereBetween('fecha_checkin', [$checkinSol, $checkoutSol])
                  ->orWhereBetween('fecha_checkout_previsto', [$checkinSol, $checkoutSol])
                  ->orWhere(function($q2) use ($checkinSol, $checkoutSol) {
                      $q2->where('fecha_checkin', '<=', $checkinSol)
                         ->where('fecha_checkout_previsto', '>=', $checkoutSol);
                  });
            })->first();

        if ($conflicto) {
            $fechaConf = Carbon::parse($conflicto->fecha_checkin)->format('d/m/Y');
            return response()->json([
                'success' => false,
                'mensaje' => "⚠️ La habitación {$habitacion->numero} ya tiene una reserva/ocupación desde el {$fechaConf}. Elige otras fechas u otra habitación."
            ], 422);
        }

        $reserva = HotelReserva::create([
            'empresa_id'              => $empresaId,
            'habitacion_id'           => $habitacion->id,
            'huesped_id'              => $huesped->id,
            'usuario_id'              => auth()->id(),
            'codigo'                  => $codigo,
            'fecha_checkin'           => $request->fecha_checkin,
            'fecha_checkout_previsto' => $request->fecha_checkout,
            'num_huespedes'           => $request->num_huespedes ?? 1,
            'precio_noche'            => $precioNoche2,
            'num_noches'              => $noches,
            'total'                   => $total,
            'monto_pagado'            => $request->adelanto ?? 0,
            'estado'                  => 'reservado',
            'estado_pago'             => ($request->adelanto ?? 0) > 0 ? 'parcial' : 'pendiente',
            'observaciones'           => $request->observaciones,
        ]);

        if (($request->adelanto ?? 0) > 0) {
            HotelPago::create([
                'reserva_id'  => $reserva->id,
                'usuario_id'  => auth()->id(),
                'monto'       => $request->adelanto,
                'metodo_pago' => $request->metodo_adelanto ?? 'efectivo',
                'referencia'  => 'Adelanto reserva ' . $codigo,
            ]);
        }

        return response()->json(['success' => true, 'reserva' => $reserva, 'codigo' => $codigo, 'mensaje' => 'Reserva registrada ✅']);
    }

    // ── CONFIRMAR CHECKIN DE RESERVA ──
    public function confirmarCheckin(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $reserva   = HotelReserva::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        $reserva->update(['estado' => 'checkin', 'fecha_checkin' => now()]);
        $reserva->habitacion->update(['estado' => 'ocupada']);
        return response()->json(['success' => true, 'mensaje' => 'Check-in confirmado ✅']);
    }

    public function cancelarReserva(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $reserva   = HotelReserva::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        $reserva->habitacion->update(['estado' => 'disponible']);
        $reserva->update(['estado' => 'cancelado']);
        return response()->json(['success' => true, 'mensaje' => 'Reserva cancelada']);
    }

    // ── EXTENSION DE ESTADIA ──
    public function extenderEstadia(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $reserva   = HotelReserva::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        $nuevaFecha = $request->nueva_fecha_checkout;
        $noches = max(1, Carbon::parse($reserva->fecha_checkin)->startOfDay()->diffInDays(Carbon::parse($nuevaFecha)->startOfDay()));
        $nuevoTotal = ($reserva->precio_noche * $noches) + HotelCargo::where('reserva_id', $id)->sum('subtotal');
        $reserva->update([
            'fecha_checkout_previsto' => $nuevaFecha,
            'num_noches'              => $noches,
            'total'                   => $nuevoTotal,
        ]);
        return response()->json(['success' => true, 'mensaje' => 'Estadía extendida hasta ' . $nuevaFecha, 'nuevo_total' => $nuevoTotal]);
    }

    // ── CAMBIO DE HABITACION ──
    public function cambiarHabitacion(Request $request, $id)
    {
        $empresaId    = auth()->user()->empresa_id;
        $reserva      = HotelReserva::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        $nuevaHab     = HotelHabitacion::where('id', $request->nueva_habitacion_id)->where('empresa_id', $empresaId)->firstOrFail();

        if ($nuevaHab->estado !== 'disponible') {
            return response()->json(['success' => false, 'mensaje' => 'La habitación no está disponible'], 422);
        }

        $habAnterior = $reserva->habitacion;
        $habAnterior->update(['estado' => 'limpieza']);

        $noches = $reserva->num_noches;
        $nuevoTotal = ($nuevaHab->tipo->precio_noche * $noches) + HotelCargo::where('reserva_id', $id)->sum('subtotal');

        $reserva->update([
            'habitacion_id' => $nuevaHab->id,
            'precio_noche'  => $nuevaHab->tipo->precio_noche,
            'total'         => $nuevoTotal,
        ]);
        $nuevaHab->update(['estado' => 'ocupada']);

        return response()->json(['success' => true, 'mensaje' => 'Habitación cambiada a ' . $nuevaHab->numero, 'nuevo_total' => $nuevoTotal]);
    }

    // ── PAGO PARCIAL ──
    public function registrarPago(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $reserva   = HotelReserva::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();

        HotelPago::create([
            'reserva_id'  => $reserva->id,
            'usuario_id'  => auth()->id(),
            'monto'       => $request->monto,
            'metodo_pago' => $request->metodo_pago ?? 'efectivo',
            'referencia'  => $request->referencia ?? '',
        ]);
        $reserva->increment('monto_pagado', $request->monto);
        $reserva->refresh();
        $estadoPago = $reserva->monto_pagado >= $reserva->total ? 'pagado' : 'parcial';
        $reserva->update(['estado_pago' => $estadoPago]);

        return response()->json(['success' => true, 'monto_pagado' => $reserva->monto_pagado, 'saldo' => $reserva->total - $reserva->monto_pagado, 'estado_pago' => $estadoPago]);
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

        // ── FACTURACIÓN ELECTRÓNICA ──
        $comprobantePdf = null;
        $tipoComp = $request->tipo_comprobante ?? 'ninguno';

        if (in_array($tipoComp, ['boleta', 'factura'])) {
            $empresa     = auth()->user()->empresa;
            $exonerada   = $empresa->zona_exonerada ?? false;
            $totalMonto  = round(floatval($reserva->total), 2);
            $tipoDoc     = $tipoComp === 'factura' ? '01' : '03';
            $clienteDoc  = $request->cliente_documento ?? $reserva->huesped->numero_documento;
            $clienteNom  = $request->cliente_nombre    ?? $reserva->huesped->nombre_completo;
            $clienteEmail= $request->cliente_email     ?? $reserva->huesped->email ?? '';
            $tipoDocCliente = $tipoComp === 'factura' ? '6' : '1';

            if ($exonerada) { $gravada = 0; $igv = 0; }
            else { $gravada = round($totalMonto / 1.18, 2); $igv = round($totalMonto - $gravada, 2); }
            $baseImponible = $exonerada ? $totalMonto : $gravada;

            if ($tipoComp === 'factura') {
                $serie = $empresa->serie_factura ?? 'F001';
                $corr  = ($empresa->ultimo_num_factura ?? 0) + 1;
                $empresa->increment('ultimo_num_factura');
            } else {
                $serie = $empresa->serie_boleta ?? 'B001';
                $corr  = ($empresa->ultimo_num_boleta ?? 0) + 1;
                $empresa->increment('ultimo_num_boleta');
            }

            $fileName = $empresa->ruc . '-' . $tipoDoc . '-' . $serie . '-' . str_pad($corr, 8, '0', STR_PAD_LEFT);
            $valUnit  = $exonerada ? $totalMonto : round($totalMonto / 1.18, 4);
            $igvItem  = $exonerada ? 0 : round($totalMonto - $valUnit, 2);

            // Construir items: hospedaje + cargos extras
            $cargosReserva = HotelCargo::where('reserva_id', $reserva->id)->get();
            $lineas = [];

            // Item principal: hospedaje
            $precHosp  = round($reserva->precio_noche * $reserva->num_noches, 2);
            $valHosp   = $exonerada ? $precHosp : round($precHosp / 1.18, 4);
            $igvHosp   = $exonerada ? 0 : round($precHosp - $valHosp, 2);
            $lineas[]  = [
                'cbc:ID'                  => ['_text' => '1'],
                'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => $reserva->num_noches],
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valHosp],
                'cac:PricingReference'    => ['cac:AlternativeConditionPrice' => ['cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $reserva->precio_noche], 'cbc:PriceTypeCode' => ['_text' => '01']]],
                'cac:TaxTotal' => ['cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvHosp], 'cac:TaxSubtotal' => [['cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valHosp], 'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvHosp], 'cac:TaxCategory' => ['cbc:Percent' => ['_text' => $exonerada ? '0' : '18'], 'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'], 'cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']]]]]],
                'cac:Item'  => ['cbc:Description' => ['_text' => 'Hospedaje Hab. ' . $reserva->habitacion->numero . ' — ' . $reserva->num_noches . ' noche(s)'], 'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']]],
                'cac:Price' => ['cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valHosp]],
            ];

            // Items cargos extras
            foreach ($cargosReserva as $idx => $cargo) {
                $valC  = $exonerada ? $cargo->subtotal : round($cargo->subtotal / 1.18, 4);
                $igvC  = $exonerada ? 0 : round($cargo->subtotal - $valC, 2);
                $lineas[] = [
                    'cbc:ID'                  => ['_text' => (string)($idx + 2)],
                    'cbc:InvoicedQuantity'    => ['_attributes' => ['unitCode' => 'ZZ'], '_text' => $cargo->cantidad],
                    'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valC],
                    'cac:PricingReference'    => ['cac:AlternativeConditionPrice' => ['cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $cargo->precio_unitario], 'cbc:PriceTypeCode' => ['_text' => '01']]],
                    'cac:TaxTotal' => ['cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvC], 'cac:TaxSubtotal' => [['cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valC], 'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igvC], 'cac:TaxCategory' => ['cbc:Percent' => ['_text' => $exonerada ? '0' : '18'], 'cbc:TaxExemptionReasonCode' => ['_text' => $exonerada ? '20' : '10'], 'cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']]]]]],
                    'cac:Item'  => ['cbc:Description' => ['_text' => $cargo->descripcion], 'cac:SellersItemIdentification' => ['cbc:ID' => ['_text' => 'S/C']]],
                    'cac:Price' => ['cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $valC]],
                ];
            }

            $documentBody = [
                'cbc:UBLVersionID'         => ['_text' => '2.1'],
                'cbc:CustomizationID'      => ['_text' => '2.0'],
                'cbc:ID'                   => ['_text' => $serie . '-' . str_pad($corr, 8, '0', STR_PAD_LEFT)],
                'cbc:IssueDate'            => ['_text' => now()->format('Y-m-d')],
                'cbc:InvoiceTypeCode'      => ['_attributes' => ['listID' => '0101'], '_text' => $tipoDoc],
                'cbc:Note'                 => ['_attributes' => ['languageLocaleID' => '1000'], '_text' => strtoupper($this->numeroALetras($totalMonto))],
                'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
                'cac:PaymentTerms'         => ['cbc:ID' => ['_text' => 'FormaPago'], 'cbc:PaymentMeansID' => ['_text' => 'Contado']],
                'cac:AccountingSupplierParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => '6'], '_text' => $empresa->ruc]],
                    'cac:PartyName'           => ['cbc:Name' => ['_text' => $empresa->nombre_comercial ?? $empresa->razon_social]],
                    'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => $empresa->razon_social], 'cac:RegistrationAddress' => ['cbc:AddressTypeCode' => ['_text' => '0000'], 'cac:AddressLine' => ['cbc:Line' => ['_text' => $empresa->direccion ?? '']]]],
                ]],
                'cac:AccountingCustomerParty' => ['cac:Party' => [
                    'cac:PartyIdentification' => ['cbc:ID' => ['_attributes' => ['schemeID' => $tipoDocCliente], '_text' => $clienteDoc]],
                    'cac:PartyLegalEntity'    => ['cbc:RegistrationName' => ['_text' => strtoupper($clienteNom)]],
                ]],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount'   => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv],
                    'cac:TaxSubtotal' => [['cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible], 'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $igv], 'cac:TaxCategory' => ['cac:TaxScheme' => ['cbc:ID' => ['_text' => $exonerada ? '9997' : '1000'], 'cbc:Name' => ['_text' => $exonerada ? 'EXO' : 'IGV'], 'cbc:TaxTypeCode' => ['_text' => 'VAT']]]]],
                ],
                'cac:LegalMonetaryTotal' => [
                    'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $baseImponible],
                    'cbc:TaxInclusiveAmount'  => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
                    'cbc:PayableAmount'       => ['_attributes' => ['currencyID' => 'PEN'], '_text' => $totalMonto],
                ],
                'cac:InvoiceLine' => $lineas,
            ];

            try {
                if ($empresa->apisunat_token && $empresa->apisunat_ruc) {
                    $response = \Illuminate\Support\Facades\Http::withHeaders(['Content-Type' => 'application/json'])
                        ->timeout(60)
                        ->post('https://back.apisunat.com/personas/v1/sendBill', [
                            'personaId'    => $empresa->apisunat_ruc,
                            'personaToken' => $empresa->apisunat_token,
                            'fileName'     => $fileName,
                            'documentBody' => $documentBody,
                        ]);
                    $data      = $response->json();
                    $aceptada  = $response->successful() && isset($data['sunatResponse']);
                    $pendiente = $response->successful() && isset($data['status']) && $data['status'] === 'PENDIENTE';
                    $comprobantePdf = $data['sunatResponse']['enlace_del_pdf'] ?? null;

                    \App\Models\ComprobanteSunat::create([
                        'empresa_id'               => $empresa->id,
                        'tipo_comprobante'         => $tipoDoc,
                        'serie'                    => $serie,
                        'numero'                   => $corr,
                        'fecha_emision'            => now()->toDateString(),
                        'cliente_tipo_documento'   => $tipoDocCliente,
                        'cliente_numero_documento' => $clienteDoc,
                        'cliente_nombre'           => strtoupper($clienteNom),
                        'cliente_email'            => $clienteEmail,
                        'total_gravada'            => $gravada,
                        'total_igv'                => $igv,
                        'total'                    => $totalMonto,
                        'aceptada_por_sunat'       => $aceptada ? 1 : 0,
                        'sunat_descripcion'        => $aceptada ? 'Aceptada' : ($pendiente ? 'Pendiente SUNAT' : json_encode($data)),
                        'enlace_pdf'               => $comprobantePdf,
                        'estado'                   => $aceptada ? 'aceptado' : ($pendiente ? 'aceptado' : 'rechazado'),
                    ]);
                } else {
                    // Sin token configurado — guardar comprobante local
                    \App\Models\ComprobanteSunat::create([
                        'empresa_id'               => $empresa->id,
                        'tipo_comprobante'         => $tipoDoc,
                        'serie'                    => $serie,
                        'numero'                   => $corr,
                        'fecha_emision'            => now()->toDateString(),
                        'cliente_tipo_documento'   => $tipoDocCliente,
                        'cliente_numero_documento' => $clienteDoc,
                        'cliente_nombre'           => strtoupper($clienteNom),
                        'cliente_email'            => $clienteEmail,
                        'total_gravada'            => $gravada,
                        'total_igv'                => $igv,
                        'total'                    => $totalMonto,
                        'aceptada_por_sunat'       => 0,
                        'sunat_descripcion'        => 'Sin token ApiSunat configurado',
                        'enlace_pdf'               => null,
                        'estado'                   => 'local',
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error facturacion hotel: ' . $e->getMessage());
            }
        }

        $reserva->load('habitacion.tipo', 'huesped', 'pagos');
        return response()->json([
            'success'        => true,
            'mensaje'        => 'Check-out realizado ✅',
            'comprobante_pdf'=> $comprobantePdf,
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

    private function numeroALetras($numero)
    {
        $entero  = (int)$numero;
        $decimal = round(($numero - $entero) * 100);
        return $this->enLetras($entero) . ' CON ' . str_pad($decimal, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
    }

    private function enLetras($n)
    {
        $u = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE','DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE'];
        $d = ['','','VEINTE','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
        $c = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS','SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];
        if ($n == 0) return 'CERO';
        if ($n == 100) return 'CIEN';
        if ($n < 16) return $u[$n];
        if ($n < 20) return 'DIECI' . $u[$n - 10];
        if ($n == 20) return 'VEINTE';
        if ($n < 30) return 'VEINTI' . $u[$n - 20];
        if ($n < 100) return $d[intdiv($n,10)] . ($n%10 ? ' Y ' . $u[$n%10] : '');
        if ($n < 1000) return $c[intdiv($n,100)] . ($n%100 ? ' ' . $this->enLetras($n%100) : '');
        if ($n < 2000) return 'MIL' . ($n%1000 ? ' ' . $this->enLetras($n%1000) : '');
        if ($n < 1000000) return $this->enLetras(intdiv($n,1000)) . ' MIL' . ($n%1000 ? ' ' . $this->enLetras($n%1000) : '');
        return (string)$n;
    }

    public function reportesPdf(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $empresa   = auth()->user()->empresa;
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->format('Y-m-d'));

        $reservas = HotelReserva::with('habitacion.tipo','huesped')
            ->where('empresa_id', $empresaId)
            ->whereBetween('fecha_checkin', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('fecha_checkin','desc')->get();

        $totalIngresos = $reservas->sum('monto_pagado');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.hotel_reporte', compact('reservas','empresa','desde','hasta','totalIngresos'));
        return $pdf->download('Reporte_Hotel_' . $desde . '_a_' . $hasta . '.pdf');
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

        $reservas = HotelReserva::with('habitacion.tipo','huesped','pagos')
            ->where('empresa_id', $empresaId)
            ->whereBetween('fecha_checkin', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('fecha_checkin','desc')->get();

        $totalHabitaciones = HotelHabitacion::where('empresa_id', $empresaId)->count();
        $totalIngresos     = $reservas->sum('monto_pagado');
        $totalReservas     = $reservas->count();
        $totalNoches       = $reservas->sum('num_noches');
        $promedioNoche     = $totalReservas > 0 ? round($totalIngresos / max($totalNoches, 1), 2) : 0;
        $ocupacionPromedio = $totalHabitaciones > 0
            ? round($reservas->whereIn('estado', ['checkin','checkout'])->count() / max($totalReservas, 1) * 100, 1)
            : 0;

        // Desglose: hospedaje vs room service
        $ingresosHospedaje = $reservas->whereIn('estado', ['checkin','checkout'])->sum(function($r) {
            return $r->precio_noche * $r->num_noches;
        });
        $ingresosRoomService = HotelCargo::whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId))
            ->whereHas('reserva', fn($q) => $q->whereBetween('fecha_checkin', [$desde . ' 00:00:00', $hasta . ' 23:59:59']))
            ->sum('subtotal');

        // Top productos más vendidos (room service)
        $topProductos = HotelCargo::with('producto')
            ->whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId)
                ->whereBetween('fecha_checkin', [$desde . ' 00:00:00', $hasta . ' 23:59:59']))
            ->selectRaw('producto_id, SUM(cantidad) as total_cantidad, SUM(subtotal) as total_monto')
            ->groupBy('producto_id')
            ->orderByDesc('total_monto')
            ->limit(5)
            ->get();

        // Ingresos por método de pago
        $pagos = \App\Models\HotelPago::whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId))
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->selectRaw('metodo_pago, SUM(monto) as total')
            ->groupBy('metodo_pago')->get();

        // Ingresos por mes (últimos 6 meses)
        $ingresosPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $ing = HotelReserva::where('empresa_id', $empresaId)
                ->whereYear('fecha_checkin', $mes->year)
                ->whereMonth('fecha_checkin', $mes->month)
                ->sum('monto_pagado');
            $ingresosPorMes[] = [
                'mes'      => $mes->locale('es')->isoFormat('MMM YY'),
                'ingresos' => round($ing, 2),
            ];
        }

        // Ocupacion por mes (últimos 6 meses)
        $ocupacionPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $diasMes = $mes->daysInMonth;
            $noches  = HotelReserva::where('empresa_id', $empresaId)
                ->whereYear('fecha_checkin', $mes->year)
                ->whereMonth('fecha_checkin', $mes->month)
                ->sum('num_noches');
            $pct = $totalHabitaciones > 0
                ? round($noches / ($totalHabitaciones * $diasMes) * 100, 1)
                : 0;
            $ocupacionPorMes[] = [
                'mes'       => $mes->locale('es')->isoFormat('MMM YY'),
                'ocupacion' => min($pct, 100),
            ];
        }

        return Inertia::render('Hotel/Reportes', compact(
            'reservas','totalIngresos','totalReservas','ocupacionPromedio',
            'desde','hasta','totalNoches','promedioNoche','pagos',
            'ingresosPorMes','ocupacionPorMes','totalHabitaciones',
            'ingresosHospedaje','ingresosRoomService','topProductos'
        ));
    }

    // ── CIERRE DIARIO ──
    public function cierreDiario(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));

        $reservas = HotelReserva::with('habitacion.tipo','huesped','pagos')
            ->where('empresa_id', $empresaId)
            ->whereDate('fecha_checkout_real', $fecha)
            ->where('estado', 'checkout')
            ->get();

        $checkins = HotelReserva::with('habitacion','huesped')
            ->where('empresa_id', $empresaId)
            ->whereDate('fecha_checkin', $fecha)
            ->get();

        $pagosHoy = \App\Models\HotelPago::whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId))
            ->whereDate('created_at', $fecha)
            ->get();

        $totalEfectivo     = $pagosHoy->where('metodo_pago', 'efectivo')->sum('monto');
        $totalYape         = $pagosHoy->where('metodo_pago', 'yape')->sum('monto');
        $totalPlin         = $pagosHoy->where('metodo_pago', 'plin')->sum('monto');
        $totalTarjeta      = $pagosHoy->where('metodo_pago', 'tarjeta')->sum('monto');
        $totalTransferencia= $pagosHoy->where('metodo_pago', 'transferencia')->sum('monto');
        $totalDia          = $pagosHoy->sum('monto');

        $habitacionesOcupadas  = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'ocupada')->count();
        $habitacionesDisponibles = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'disponible')->count();
        $habitacionesLimpieza  = HotelHabitacion::where('empresa_id', $empresaId)->where('estado', 'limpieza')->count();
        $totalHabitaciones     = HotelHabitacion::where('empresa_id', $empresaId)->count();

        return Inertia::render('Hotel/CierreDiario', compact(
            'fecha','reservas','checkins','pagosHoy',
            'totalEfectivo','totalYape','totalPlin','totalTarjeta','totalTransferencia','totalDia',
            'habitacionesOcupadas','habitacionesDisponibles','habitacionesLimpieza','totalHabitaciones'
        ));
    }

    // ── PRODUCTOS HOTEL ──
    public function productos()
    {
        $empresaId = auth()->user()->empresa_id;
        $productos = HotelProducto::where('empresa_id', $empresaId)->orderBy('categoria')->orderBy('nombre')->get();
        return Inertia::render('Hotel/Productos', compact('productos'));
    }

    public function storeProducto(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $request->validate(['nombre' => 'required|string|max:100', 'precio' => 'required|numeric|min:0']);
        HotelProducto::create([...$request->only(['nombre','descripcion','categoria','precio','stock','controla_stock','activo']), 'empresa_id' => $empresaId]);
        return redirect()->back()->with('success', 'Producto creado');
    }

    public function updateProducto(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelProducto::where('id', $id)->where('empresa_id', $empresaId)
            ->update($request->only(['nombre','descripcion','categoria','precio','stock','controla_stock','activo']));
        return redirect()->back()->with('success', 'Producto actualizado');
    }

    public function destroyProducto($id)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelProducto::where('id', $id)->where('empresa_id', $empresaId)->delete();
        return redirect()->back()->with('success', 'Producto eliminado');
    }

    // ── CARGOS A HABITACION ──
    public function storeCargo(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $request->validate(['reserva_id' => 'required', 'producto_id' => 'required', 'cantidad' => 'required|integer|min:1']);

        $producto = HotelProducto::where('id', $request->producto_id)->where('empresa_id', $empresaId)->firstOrFail();
        $subtotal = $producto->precio * $request->cantidad;

        HotelCargo::create([
            'empresa_id'      => $empresaId,
            'reserva_id'      => $request->reserva_id,
            'producto_id'     => $producto->id,
            'usuario_id'      => auth()->id(),
            'descripcion'     => $producto->nombre,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $producto->precio,
            'subtotal'        => $subtotal,
        ]);

        // Actualizar total de la reserva
        $totalCargos = HotelCargo::where('reserva_id', $request->reserva_id)->sum('subtotal');
        $reserva = \App\Models\HotelReserva::find($request->reserva_id);
        $reserva->update(['total' => ($reserva->precio_noche * $reserva->num_noches) + $totalCargos]);

        // Descontar stock si aplica
        if ($producto->controla_stock) {
            $producto->decrement('stock', $request->cantidad);
        }

        return redirect()->back()->with('success', 'Cargo agregado');
    }

    public function destroyCargo($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $cargo = HotelCargo::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        $reserva_id = $cargo->reserva_id;
        $cargo->delete();

        // Recalcular total
        $reserva = \App\Models\HotelReserva::find($reserva_id);
        $totalCargos = HotelCargo::where('reserva_id', $reserva_id)->sum('subtotal');
        $reserva->update(['total' => ($reserva->precio_noche * $reserva->num_noches) + $totalCargos]);

        return redirect()->back()->with('success', 'Cargo eliminado');
    }

    // ── HUÉSPEDES ──
    public function huespedes(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $buscar = $request->get('buscar', '');

        $huespedes = HotelHuesped::where('empresa_id', $empresaId)
            ->when($buscar, function($q) use ($buscar) {
                $q->where(function($q2) use ($buscar) {
                    $q2->where('nombre_completo', 'like', "%$buscar%")
                       ->orWhere('numero_documento', 'like', "%$buscar%")
                       ->orWhere('telefono', 'like', "%$buscar%");
                });
            })
            ->withCount('reservas')
            ->withSum(['reservas as total_gastado' => function($q) {
                $q->whereIn('estado', ['checkin','checkout']);
            }], 'monto_pagado')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Hotel/Huespedes', compact('huespedes', 'buscar'));
    }

    public function huesped($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $huesped = HotelHuesped::where('id', $id)->where('empresa_id', $empresaId)->firstOrFail();
        $reservas = HotelReserva::with('habitacion.tipo', 'pagos')
            ->where('huesped_id', $id)
            ->where('empresa_id', $empresaId)
            ->orderByDesc('fecha_checkin')
            ->get();

        $totalGastado  = $reservas->whereIn('estado', ['checkin','checkout'])->sum('monto_pagado');
        $totalEstadias = $reservas->whereIn('estado', ['checkin','checkout'])->count();
        $totalNoches   = $reservas->whereIn('estado', ['checkin','checkout'])->sum('num_noches');

        return Inertia::render('Hotel/HuespedDetalle', compact('huesped','reservas','totalGastado','totalEstadias','totalNoches'));
    }

    // ── TARIFAS TEMPORADA ──
    public function tarifas()
    {
        $empresaId = auth()->user()->empresa_id;
        $tarifas = HotelTarifaTemporada::with('tipo')
            ->where('empresa_id', $empresaId)
            ->orderBy('fecha_inicio')->get();
        $tipos = HotelTipoHabitacion::where('empresa_id', $empresaId)->where('activo', true)->get();
        return Inertia::render('Hotel/Tarifas', compact('tarifas', 'tipos'));
    }

    public function storeTarifa(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelTarifaTemporada::create([
            ...$request->only(['nombre','tipo_id','fecha_inicio','fecha_fin','precio_noche','color','descripcion']),
            'empresa_id' => $empresaId,
            'activo'     => true,
        ]);
        return back()->with('success', 'Tarifa creada ✅');
    }

    public function updateTarifa(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelTarifaTemporada::where('id', $id)->where('empresa_id', $empresaId)
            ->update($request->only(['nombre','tipo_id','fecha_inicio','fecha_fin','precio_noche','color','activo','descripcion']));
        return back()->with('success', 'Tarifa actualizada ✅');
    }

    public function destroyTarifa($id)
    {
        $empresaId = auth()->user()->empresa_id;
        HotelTarifaTemporada::where('id', $id)->where('empresa_id', $empresaId)->delete();
        return back()->with('success', 'Tarifa eliminada');
    }
}