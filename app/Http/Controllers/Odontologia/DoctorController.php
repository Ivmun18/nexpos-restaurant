<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoHorarioDoctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index() {
        $doctores = OdontoDoctor::where('empresa_id', $this->empresaId())
            ->with('horarios')
            ->orderBy('nombre')
            ->get();
        return Inertia::render('Odontologia/Doctores/Index', compact('doctores'));
    }

    public function store(Request $request) {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'especialidad'=> 'nullable|string|max:100',
            'colegiatura' => 'nullable|string|max:50',
            'telefono'    => 'nullable|string|max:20',
            'email'       => 'nullable|email',
        ]);
        $doctor = OdontoDoctor::create([
            ...$request->only(['nombre','especialidad','colegiatura','telefono','email']),
            'empresa_id' => $this->empresaId(),
        ]);
        if ($request->horarios) {
            foreach ($request->horarios as $h) {
                OdontoHorarioDoctor::create([
                    'doctor_id'   => $doctor->id,
                    'dia_semana'  => $h['dia_semana'],
                    'hora_inicio' => $h['hora_inicio'],
                    'hora_fin'    => $h['hora_fin'],
                ]);
            }
        }
        return back()->with('success', 'Doctor registrado correctamente.');
    }

    public function update(Request $request, $id) {
        $doctor = OdontoDoctor::where('empresa_id', $this->empresaId())->findOrFail($id);
        $doctor->update($request->only(['nombre','especialidad','colegiatura','telefono','email','activo']));
        if ($request->horarios !== null) {
            OdontoHorarioDoctor::where('doctor_id', $id)->delete();
            foreach ($request->horarios as $h) {
                OdontoHorarioDoctor::create([
                    'doctor_id'   => $id,
                    'dia_semana'  => $h['dia_semana'],
                    'hora_inicio' => $h['hora_inicio'],
                    'hora_fin'    => $h['hora_fin'],
                ]);
            }
        }
        return back()->with('success', 'Doctor actualizado.');
    }

    public function estadisticas(Request $request) {
        $eid    = $this->empresaId();
        $desde  = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta  = $request->get('hasta', now()->toDateString());

        $doctores = \App\Models\Odontologia\OdontoDoctor::where('empresa_id', $eid)->where('activo', true)->get();

        $stats = $doctores->map(function($doctor) use ($eid, $desde, $hasta) {
            $citas = \App\Models\Odontologia\OdontoCita::where('empresa_id', $eid)
                ->where('doctor_id', $doctor->id)
                ->whereBetween('fecha_hora', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                ->get();

            $presupuestos = \App\Models\Odontologia\OdontoPresupuesto::with('items')
                ->where('empresa_id', $eid)
                ->where('doctor_id', $doctor->id)
                ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                ->get();

            $pagos = \App\Models\Odontologia\OdontoPago::where('empresa_id', $eid)
                ->whereBetween('fecha', [$desde, $hasta])
                ->whereIn('presupuesto_id', $presupuestos->pluck('id'))
                ->where('estado', 'pagado')
                ->sum('monto_total');

            $tratamientos = $presupuestos->flatMap->items
                ->groupBy('descripcion')
                ->map(fn($g) => ['nombre' => $g->first()->descripcion, 'cantidad' => $g->count()])
                ->sortByDesc('cantidad')->take(5)->values();

            return [
                'id'              => $doctor->id,
                'nombre'          => $doctor->nombre,
                'especialidad'    => $doctor->especialidad ?? 'General',
                'total_citas'     => $citas->count(),
                'citas_completadas'=> $citas->where('estado','completada')->count(),
                'citas_canceladas' => $citas->where('estado','cancelada')->count(),
                'total_ingresos'  => round($pagos, 2),
                'total_presupuestos' => $presupuestos->count(),
                'tratamientos_top'=> $tratamientos,
            ];
        })->sortByDesc('total_ingresos')->values();

        return \Inertia\Inertia::render('Odontologia/Doctores/Estadisticas', compact('stats','desde','hasta','doctores'));
    }

    public function destroy($id) {
        $doctor = OdontoDoctor::where('empresa_id', $this->empresaId())->findOrFail($id);
        $doctor->update(['activo' => false]);
        return back()->with('success', 'Doctor desactivado.');
    }
}
