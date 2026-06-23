<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPaciente;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoPresupuesto;
use App\Models\Odontologia\OdontoRadiografia;
use App\Models\Empresa;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PortalPacienteController extends Controller {

    // Generar o regenerar token del paciente
    public function generarToken($id) {
        $paciente = OdontoPaciente::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $paciente->update(['portal_token' => Str::random(32)]);
        return response()->json([
            'token' => $paciente->portal_token,
            'url'   => url("/portal/{$paciente->portal_token}")
        ]);
    }

    // Vista pública del portal
    public function show($token) {
        $paciente = OdontoPaciente::where('portal_token', $token)->where('activo', true)->firstOrFail();
        $empresa  = Empresa::find($paciente->empresa_id);

        $citas = OdontoCita::with('doctor')
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha_hora')->limit(10)->get();

        $presupuestos = OdontoPresupuesto::with(['items','doctor'])
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha')->get();

        $radiografias = OdontoRadiografia::where('paciente_id', $paciente->id)
            ->orderByDesc('fecha')->get();

        return Inertia::render('Portal/Paciente', compact(
            'paciente','empresa','citas','presupuestos','radiografias'
        ));
    }
}
