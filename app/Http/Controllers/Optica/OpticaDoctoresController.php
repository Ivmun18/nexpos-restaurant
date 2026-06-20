<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaDoctor;
use Inertia\Inertia;

class OpticaDoctoresController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $doctores = OpticaDoctor::where('empresa_id', $empresa_id)
            ->withCount('fichas')->orderBy('nombre')->get();
        return Inertia::render('Optica/Doctores/Index', compact('doctores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'=>'required|string|max:100',
            'especialidad'=>'nullable|string|max:100',
            'colegiatura'=>'nullable|string|max:30',
            'telefono'=>'nullable|string|max:20',
            'email'=>'nullable|email',
        ]);
        $data['empresa_id'] = auth()->user()->empresa_id;
        OpticaDoctor::create($data);
        return back()->with('success','Doctor registrado.');
    }

    public function update(Request $request, $id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $doc = OpticaDoctor::where('empresa_id',$empresa_id)->findOrFail($id);
        $data = $request->validate([
            'nombre'=>'required|string|max:100',
            'especialidad'=>'nullable|string|max:100',
            'colegiatura'=>'nullable|string|max:30',
            'telefono'=>'nullable|string|max:20',
            'email'=>'nullable|email',
            'activo'=>'boolean',
        ]);
        $doc->update($data);
        return back()->with('success','Doctor actualizado.');
    }

    public function destroy($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $doc = OpticaDoctor::where('empresa_id',$empresa_id)->findOrFail($id);
        $doc->update(['activo'=>false]);
        return back()->with('success','Doctor desactivado.');
    }
}
