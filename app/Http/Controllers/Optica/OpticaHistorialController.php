<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaHistorial;
use App\Models\OpticaPaciente;
use App\Models\OpticaDoctor;
use App\Models\OpticaFicha;
use Inertia\Inertia;

class OpticaHistorialController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $q = $request->get('q');
        $historiales = OpticaHistorial::where('empresa_id',$empresa_id)
            ->with(['paciente','doctor'])
            ->when($q, fn($query) => $query->whereHas('paciente', fn($p) =>
                $p->where('nombre','like',"%$q%")->orWhere('apellidos','like',"%$q%")
            ))
            ->latest()->paginate(20)->withQueryString();
        $pacientes = OpticaPaciente::where('empresa_id',$empresa_id)->orderBy('apellidos')->get(['id','nombre','apellidos','dni']);
        $doctores = OpticaDoctor::where('empresa_id',$empresa_id)->where('activo',true)->orderBy('nombre')->get(['id','nombre']);
        return Inertia::render('Optica/Historial/Index', compact('historiales','pacientes','doctores','q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id'=>'required|integer',
            'doctor_id'=>'nullable|integer',
            'ficha_id'=>'nullable|integer',
            'fecha'=>'required|date',
            'motivo_consulta'=>'nullable|string',
            'antecedentes'=>'nullable|string',
            'diagnostico'=>'nullable|string',
            'tratamiento'=>'nullable|string',
            'observaciones'=>'nullable|string',
            'proxima_cita'=>'nullable|date',
        ]);
        $data['empresa_id'] = auth()->user()->empresa_id;
        OpticaHistorial::create($data);
        return back()->with('success','Historial registrado.');
    }

    public function show($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $historial = OpticaHistorial::where('empresa_id',$empresa_id)->with(['paciente','doctor','ficha'])->findOrFail($id);
        return Inertia::render('Optica/Historial/Show', compact('historial'));
    }

    public function update(Request $request, $id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $h = OpticaHistorial::where('empresa_id',$empresa_id)->findOrFail($id);
        $data = $request->validate([
            'doctor_id'=>'nullable|integer',
            'fecha'=>'required|date',
            'motivo_consulta'=>'nullable|string',
            'antecedentes'=>'nullable|string',
            'diagnostico'=>'nullable|string',
            'tratamiento'=>'nullable|string',
            'observaciones'=>'nullable|string',
            'proxima_cita'=>'nullable|date',
        ]);
        $h->update($data);
        return back()->with('success','Historial actualizado.');
    }

    public function destroy($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        OpticaHistorial::where('empresa_id',$empresa_id)->findOrFail($id)->delete();
        return back()->with('success','Historial eliminado.');
    }
}
