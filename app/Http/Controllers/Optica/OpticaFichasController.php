<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaFicha;
use App\Models\OpticaPaciente;
use App\Models\OpticaReceta;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OpticaFichasController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $fichas = OpticaFicha::where('empresa_id',$empresa_id)
            ->with('paciente')->latest()->paginate(20);
        $pacientes = OpticaPaciente::where('empresa_id',$empresa_id)
            ->orderBy('apellidos')->get(['id','nombre','apellidos','dni']);
        return Inertia::render('Optica/Fichas/Index', compact('fichas','pacientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id'=>'required|integer',
            'fecha'=>'required|date',
            'od_esfera'=>'nullable|numeric','od_cilindro'=>'nullable|numeric',
            'od_eje'=>'nullable|integer','od_adicion'=>'nullable|numeric','od_av'=>'nullable|string',
            'oi_esfera'=>'nullable|numeric','oi_cilindro'=>'nullable|numeric',
            'oi_eje'=>'nullable|integer','oi_adicion'=>'nullable|numeric','oi_av'=>'nullable|string',
            'div'=>'nullable|numeric','observaciones'=>'nullable|string',
        ]);
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['user_id'] = auth()->id();
        $ficha = OpticaFicha::create($data);

        // Generar receta automáticamente
        $count = OpticaReceta::where('empresa_id',$data['empresa_id'])->count();
        OpticaReceta::create([
            'empresa_id'    => $data['empresa_id'],
            'paciente_id'   => $data['paciente_id'],
            'ficha_id'      => $ficha->id,
            'numero_receta' => 'REC-'.str_pad($count+1,4,'0',STR_PAD_LEFT),
            'fecha'         => $data['fecha'],
            'tipo'          => isset($data['od_adicion']) ? 'progresivo' : 'lejos',
            'indicaciones'  => 'Uso permanente.',
        ]);

        return back()->with('success','Ficha registrada y receta generada.');
    }

    public function show($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $ficha = OpticaFicha::where('empresa_id',$empresa_id)->with('paciente','recetas')->findOrFail($id);
        return Inertia::render('Optica/Fichas/Show', compact('ficha'));
    }

    public function update(Request $request, $id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $ficha = OpticaFicha::where('empresa_id',$empresa_id)->findOrFail($id);
        $data = $request->validate([
            'fecha'=>'required|date',
            'od_esfera'=>'nullable|numeric','od_cilindro'=>'nullable|numeric',
            'od_eje'=>'nullable|integer','od_adicion'=>'nullable|numeric','od_av'=>'nullable|string',
            'oi_esfera'=>'nullable|numeric','oi_cilindro'=>'nullable|numeric',
            'oi_eje'=>'nullable|integer','oi_adicion'=>'nullable|numeric','oi_av'=>'nullable|string',
            'div'=>'nullable|numeric','observaciones'=>'nullable|string',
        ]);
        $ficha->update($data);
        return back()->with('success','Ficha actualizada.');
    }

    public function destroy($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $ficha = OpticaFicha::where('empresa_id',$empresa_id)->findOrFail($id);
        $ficha->delete();
        return back()->with('success','Ficha eliminada.');
    }

    public function pdf($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $ficha = OpticaFicha::where('empresa_id',$empresa_id)->with('paciente')->findOrFail($id);
        $pdf = Pdf::loadView('optica.ficha-pdf', compact('ficha'))->setPaper([0,0,419.53,595.28]);
        return $pdf->stream('ficha-'.$ficha->id.'.pdf');
    }
}
