<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MozoController extends Controller
{
    /**
     * Listado de mozos con métricas básicas.
     */
    public function index(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $query = User::query()
            ->mozos()
            ->deEmpresa($empresaId)
            ->withCount(['mesas', 'pedidos']);

        if ($request->filled('buscar')) {
            $buscar = $request->string('buscar')->toString();
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('dni', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->boolean('estado'));
        }

        $mozos = $query
            ->orderBy('activo', 'desc')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Mozos/Index', [
            'mozos'   => $mozos,
            'filtros' => $request->only(['buscar', 'estado']),
        ]);
    }

    /**
     * Crear nuevo mozo.
     */
    public function store(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'password'      => ['required', 'string', 'min:6'],
            'dni'           => ['nullable', 'string', 'max:20'],
            'telefono'      => ['nullable', 'string', 'max:20'],
            'fecha_ingreso' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        User::create([
            'empresa_id'    => $empresaId,
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'rol'           => 'mozo',
            'dni'           => $data['dni'] ?? null,
            'telefono'      => $data['telefono'] ?? null,
            'fecha_ingreso' => $data['fecha_ingreso'] ?? now()->toDateString(),
            'activo'        => true,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return back()->with('success', 'Mozo registrado correctamente.');
    }

    /**
     * Actualizar mozo.
     */
    public function update(Request $request, User $mozo)
    {
        $this->autorizarMismaEmpresa($request, $mozo);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($mozo->id)],
            'password'      => ['nullable', 'string', 'min:6'],
            'dni'           => ['nullable', 'string', 'max:20'],
            'telefono'      => ['nullable', 'string', 'max:20'],
            'fecha_ingreso' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $update = collect($data)->except('password')->toArray();

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $mozo->update($update);

        return back()->with('success', 'Mozo actualizado.');
    }

    /**
     * Activar / desactivar (soft).
     */
    public function toggleActivo(Request $request, User $mozo)
    {
        $this->autorizarMismaEmpresa($request, $mozo);

        $mozo->update(['activo' => ! $mozo->activo]);

        return back()->with(
            'success',
            $mozo->activo ? 'Mozo activado.' : 'Mozo desactivado.'
        );
    }

    /**
     * Asignar mesas al mozo (sync de la pivote).
     */
    public function asignarMesas(Request $request, User $mozo)
    {
        $this->autorizarMismaEmpresa($request, $mozo);

        $data = $request->validate([
            'mesa_ids'   => ['array'],
            'mesa_ids.*' => ['integer', 'exists:mesas,id'],
        ]);

        $mesaIds = $data['mesa_ids'] ?? [];

        // Validar que las mesas pertenezcan a la empresa del admin
        // (ajusta si tu tabla mesas no tiene empresa_id)
        if (! empty($mesaIds) && Schema::hasColumn('mesas', 'empresa_id')) {
            $validas = Mesa::whereIn('id', $mesaIds)
                ->where('empresa_id', $request->user()->empresa_id)
                ->pluck('id')
                ->toArray();
            $mesaIds = $validas;
        }

        $mozo->mesas()->sync($mesaIds);

        return back()->with('success', 'Asignación de mesas actualizada.');
    }

    /**
     * Detalle del mozo + métricas.
     */
    public function show(Request $request, User $mozo)
    {
        $this->autorizarMismaEmpresa($request, $mozo);

        $mozo->load(['mesas:id,numero', 'turnos' => fn ($q) => $q->latest()->limit(10)]);

        $hoy     = now()->startOfDay();
        $mesIni  = now()->startOfMonth();

        // Ajusta los nombres de columnas según tu schema real de pedidos.
        // Asumo: pedidos.user_id, pedidos.total, pedidos.estado ('pagado' / 'cobrado'), pedidos.created_at
        $base = Pedido::where('user_id', $mozo->id);

        $metricas = [
            'pedidos_hoy'        => (clone $base)->whereDate('created_at', $hoy)->count(),
            'ventas_hoy'         => (clone $base)->whereDate('created_at', $hoy)->sum('total'),
            'pedidos_mes'        => (clone $base)->where('created_at', '>=', $mesIni)->count(),
            'ventas_mes'         => (clone $base)->where('created_at', '>=', $mesIni)->sum('total'),
            'ticket_promedio'    => (float) (clone $base)
                ->where('created_at', '>=', $mesIni)
                ->avg('total'),
            'mesas_asignadas'    => $mozo->mesas->count(),
            'turnos_mes'         => (clone $mozo->turnos())->where('apertura', '>=', $mesIni)->count(),
        ];

        // Ventas por día (últimos 14 días) para gráfico
        $ventasDiarias = (clone $base)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as pedidos')
            )
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $todasLasMesas = Mesa::query()
            ->when(
                Schema::hasColumn('mesas', 'empresa_id'),
                fn ($q) => $q->where('empresa_id', $request->user()->empresa_id)
            )
            ->orderBy('numero')
            ->get(['id', 'numero']);

        return Inertia::render('Admin/Mozos/Detalle', [
            'mozo'           => $mozo,
            'metricas'       => $metricas,
            'ventas_diarias' => $ventasDiarias,
            'todas_mesas'    => $todasLasMesas,
            'mesas_asignadas_ids' => $mozo->mesas->pluck('id'),
        ]);
    }

    /**
     * Verifica que el mozo pertenezca a la empresa del admin.
     */
    private function autorizarMismaEmpresa(Request $request, User $mozo): void
    {
        if ($mozo->empresa_id !== $request->user()->empresa_id) {
            abort(403);
        }

        if ($mozo->rol !== 'mozo') {
            abort(404);
        }
    }
}
