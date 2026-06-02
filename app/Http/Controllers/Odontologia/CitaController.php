<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CitaController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $fecha = $request->get('fecha', now()->toDateString());
        $doctor_id = $request->get('doctor_id');

        $citas = OdontoCita::with(['paciente','doctor'])
            ->where('empresa_id', $empresaId)
            ->whereDate('fecha_hora', $fecha)
            ->when($doctor_id, fn($q) => $q->where('doctor_id', $doctor_id))
            ->orderBy('fecha_hora')
            ->get();

        $doctores = OdontoDoctor::where('empresa_id', $empresaId)->where('activo', true)->get(['id','nombre']);

        return Inertia::render('Odontologia/Citas/Index', compact('citas','doctores','fecha','doctor_id'));
    }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required|exists:odonto_pacientes,id',
            'doctor_id'   => 'required|exists:odonto_doctores,id',
            'fecha_hora'  => 'required|date',
            'duracion_min'=> 'required|integer|min:15',
        ]);
        OdontoCita::create([
            ...$request->only(['paciente_id','doctor_id','fecha_hora','duracion_min','motivo','observaciones']),
            'empresa_id' => $this->empresaId(),
            'estado'     => 'programada',
        ]);
        return back()->with('success', 'Cita registrada correctamente.');
    }

    public function update(Request $request, $id) {
        $cita = OdontoCita::where('empresa_id', $this->empresaId())->findOrFail($id);
        $cita->update($request->only(['fecha_hora','duracion_min','estado','motivo','observaciones','doctor_id']));
        return back()->with('success', 'Cita actualizada.');
    }

    public function destroy($id) {
        $cita = OdontoCita::where('empresa_id', $this->empresaId())->findOrFail($id);
        $cita->update(['estado' => 'cancelada']);
        return back()->with('success', 'Cita cancelada.');
    }
}
