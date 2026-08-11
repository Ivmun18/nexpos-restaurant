<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoConsultorio;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoDoctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConsultorioController extends Controller {
    private function empresaId() { return auth()->user()->empresa_id; }

    public function index(Request $request) {
        $eid   = $this->empresaId();
        $fecha = $request->get('fecha', now()->toDateString());

        $consultorios = OdontoConsultorio::where('empresa_id', $eid)
            ->where('activo', true)->orderBy('orden')->get();

        $doctores = OdontoDoctor::where('empresa_id', $eid)
            ->where('activo', true)->get(['id','nombre','especialidad']);

        // Citas del día agrupadas por consultorio
        $citas = OdontoCita::with(['paciente','doctor','consultorio'])
            ->where('empresa_id', $eid)
            ->whereDate('fecha_hora', $fecha)
            ->orderBy('fecha_hora')
            ->get();

        return Inertia::render('Odontologia/Consultorios/Index', compact(
            'consultorios','doctores','citas','fecha'
        ));
    }

    public function store(Request $request) {
        $request->validate(['nombre' => 'required|string|max:80']);
        $orden = OdontoConsultorio::where('empresa_id', $this->empresaId())->count() + 1;
        OdontoConsultorio::create([
            'empresa_id' => $this->empresaId(),
            'nombre'     => $request->nombre,
            'color'      => $request->color ?? '#8B5CF6',
            'orden'      => $orden,
        ]);
        return back()->with('success', 'Consultorio creado');
    }

    public function update(Request $request, $id) {
        $c = OdontoConsultorio::where('empresa_id', $this->empresaId())->findOrFail($id);
        $c->update($request->only(['nombre','color','orden','activo']));
        return back()->with('success', 'Consultorio actualizado');
    }

    public function destroy($id) {
        $c = OdontoConsultorio::where('empresa_id', $this->empresaId())->findOrFail($id);
        $c->delete();
        return back()->with('success', 'Consultorio eliminado');
    }
}
