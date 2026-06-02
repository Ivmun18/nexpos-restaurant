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

    public function destroy($id) {
        $doctor = OdontoDoctor::where('empresa_id', $this->empresaId())->findOrFail($id);
        $doctor->update(['activo' => false]);
        return back()->with('success', 'Doctor desactivado.');
    }
}
