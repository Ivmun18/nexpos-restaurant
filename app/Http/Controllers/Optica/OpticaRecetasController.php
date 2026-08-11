<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaReceta;
use App\Models\OpticaPaciente;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OpticaRecetasController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $recetas = OpticaReceta::where('empresa_id',$empresa_id)
            ->with('paciente','ficha')->latest()->paginate(20);
        return Inertia::render('Optica/Recetas/Index', compact('recetas'));
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $data = $request->validate([
            'paciente_id'=>'required|integer',
            'ficha_id'=>'nullable|integer',
            'fecha'=>'required|date',
            'tipo'=>'required|in:lejos,cerca,bifocal,progresivo',
            'indicaciones'=>'nullable|string',
        ]);
        $count = OpticaReceta::where('empresa_id',$empresa_id)->count();
        $data['empresa_id'] = $empresa_id;
        $data['numero_receta'] = 'REC-'.str_pad($count+1,4,'0',STR_PAD_LEFT);
        OpticaReceta::create($data);
        return back()->with('success','Receta creada.');
    }

    public function pdf($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $receta = OpticaReceta::where('empresa_id',$empresa_id)->with('paciente','ficha')->findOrFail($id);
        $pdf = Pdf::loadView('optica.receta-pdf', compact('receta'))->setPaper([0,0,419.53,595.28]);
        return $pdf->stream('receta-'.$receta->numero_receta.'.pdf');
    }

    public function destroy($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $receta = OpticaReceta::where('empresa_id',$empresa_id)->findOrFail($id);
        $receta->delete();
        return back()->with('success','Receta eliminada.');
    }
}
