<?php
namespace App\Http\Controllers\Gimnasio;
use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioMiembro;
use App\Models\Gimnasio\GimnasioPlan;
use App\Models\Gimnasio\GimnasioPago;
use App\Models\ComprobanteSunat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MiembroController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $miembros = GimnasioMiembro::where('empresa_id', $empresa_id)
            ->with('plan')
            ->orderBy('nombre')
            ->get()
            ->map(function($m) {
                $m->dias_restantes = $m->membrecia_vencimiento
                    ? Carbon::now()->diffInDays($m->membrecia_vencimiento, false)
                    : null;
                return $m;
            });
        $planes = GimnasioPlan::where('empresa_id', $empresa_id)->where('activo', true)->get();
        return Inertia::render('Gimnasio/Miembros/Index', compact('miembros', 'planes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni'       => 'nullable|string|max:20',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'nullable|email',
            'plan_id'   => 'nullable|exists:gimnasio_planes,id',
        ]);
        $empresa_id = auth()->user()->empresa_id;
        $data = $request->all();
        $data['empresa_id'] = $empresa_id;
        $data['codigo_qr'] = 'GYM-' . strtoupper(uniqid());
        if ($request->plan_id && $request->membrecia_inicio) {
            $plan = GimnasioPlan::find($request->plan_id);
            $data['membrecia_vencimiento'] = Carbon::parse($request->membrecia_inicio)->addDays($plan->duracion_dias);
            $data['estado'] = 'activo';
        } else {
            $data['estado'] = 'vencido'; // sin plan = pago por sesión
        }
        GimnasioMiembro::create($data);
        return back()->with('success', 'Miembro registrado correctamente.');
    }

    public function update(Request $request, GimnasioMiembro $miembro)
    {
        $miembro->update($request->all());
        return back()->with('success', 'Miembro actualizado.');
    }

    public function destroy(GimnasioMiembro $miembro)
    {
        $miembro->update(['activo' => false]);
        return back()->with('success', 'Miembro desactivado.');
    }

    public function renovar(Request $request, GimnasioMiembro $miembro)
    {
        $request->validate([
            'plan_id'      => 'required|exists:gimnasio_planes,id',
            'monto'        => 'required|numeric|min:0',
            'metodo_pago'  => 'required|in:efectivo,yape,plin,transferencia,tarjeta',
            'referencia'   => 'nullable|string',
            'fecha_pago'   => 'required|date',
        ]);
        $empresa_id = auth()->user()->empresa_id;
        $plan = GimnasioPlan::find($request->plan_id);
        $inicio = Carbon::now()->toDateString();
        $fin    = Carbon::now()->addDays($plan->duracion_dias)->toDateString();

        GimnasioPago::create([
            'empresa_id'     => $empresa_id,
            'miembro_id'     => $miembro->id,
            'plan_id'        => $plan->id,
            'monto'          => $request->monto,
            'metodo_pago'    => $request->metodo_pago,
            'referencia'     => $request->referencia,
            'fecha_pago'     => $request->fecha_pago,
            'periodo_inicio' => $inicio,
            'periodo_fin'    => $fin,
            'estado'         => 'pagado',
            'usuario_id'     => auth()->id(),
        ]);

        $miembro->update([
            'plan_id'               => $plan->id,
            'membrecia_inicio'      => $inicio,
            'membrecia_vencimiento' => $fin,
            'estado'                => 'activo',
        ]);

        return back()->with('success', 'Membresía renovada hasta ' . Carbon::parse($fin)->format('d/m/Y'));
    }
    public function recibo($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $pago = \App\Models\Gimnasio\GimnasioPago::with('miembro', 'plan')
            ->where('id', $id)
            ->whereHas('miembro', fn($q) => $q->where('empresa_id', $empresa_id))
            ->firstOrFail();

        $empresa = auth()->user()->empresa;
        $logoBase64 = '';
        if ($empresa->logo) {
            $logoPath = public_path('storage/' . $empresa->logo);
            if (file_exists($logoPath)) {
                $ext  = pathinfo($logoPath, PATHINFO_EXTENSION);
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
            'pago' => [
                'id'          => $pago->id,
                'monto'       => $pago->monto,
                'metodo_pago' => $pago->metodo_pago,
                'fecha_pago'  => $pago->fecha_pago,
                'plan'        => $pago->plan?->nombre ?? 'Sesion diaria',
                'periodo_inicio' => $pago->periodo_inicio,
                'periodo_fin'    => $pago->periodo_fin,
            ],
            'miembro' => [
                'nombre'   => $pago->miembro->nombre . ' ' . $pago->miembro->apellidos,
                'dni'      => $pago->miembro->dni,
                'telefono' => $pago->miembro->telefono,
            ],
        ]);
    }

}