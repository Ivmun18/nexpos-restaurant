<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoTratamientoCatalogo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TratamientoController extends Controller {
    private function empresaId() {
        return auth()->user()->empresa_id;
    }

    public function index() {
        $tratamientos = OdontoTratamientoCatalogo::where('empresa_id', $this->empresaId())
            ->orderBy('categoria')->orderBy('nombre')->get();
        $categorias = $tratamientos->pluck('categoria')->unique()->values();
        return Inertia::render('Odontologia/Tratamientos/Index', compact('tratamientos','categorias'));
    }

    public function store(Request $request) {
        $request->validate([
            'nombre'            => 'required|string|max:150',
            'categoria'         => 'required|string|max:80',
            'precio'            => 'required|numeric|min:0',
            'duracion_minutos'  => 'required|integer|min:5',
        ]);
        OdontoTratamientoCatalogo::create([
            ...$request->only('codigo','nombre','categoria','descripcion','precio','duracion_minutos'),
            'empresa_id' => $this->empresaId(),
            'activo'     => true,
        ]);
        return back()->with('success','Tratamiento creado');
    }

    public function update(Request $request, $id) {
        $t = OdontoTratamientoCatalogo::where('empresa_id', $this->empresaId())->findOrFail($id);
        $request->validate([
            'nombre'           => 'required|string|max:150',
            'categoria'        => 'required|string|max:80',
            'precio'           => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:5',
        ]);
        $t->update($request->only('codigo','nombre','categoria','descripcion','precio','duracion_minutos','activo'));
        return back()->with('success','Tratamiento actualizado');
    }

    public function destroy($id) {
        $t = OdontoTratamientoCatalogo::where('empresa_id', $this->empresaId())->findOrFail($id);
        $t->delete();
        return back()->with('success','Tratamiento eliminado');
    }

    public function lista() {
        $data = OdontoTratamientoCatalogo::where('empresa_id', $this->empresaId())
            ->where('activo', true)->orderBy('nombre')->get(['id','nombre','categoria','precio','duracion_minutos']);
        return response()->json($data);
    }
}
