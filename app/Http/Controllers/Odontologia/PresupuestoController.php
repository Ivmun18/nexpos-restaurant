<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPresupuesto;
use App\Models\Odontologia\OdontoPresupuestoItem;
use App\Models\Odontologia\OdontoTratamientoCatalogo;
use App\Models\Odontologia\OdontoDoctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresupuestoController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $presupuestos = OdontoPresupuesto::with(['paciente','doctor','items'])
            ->where('empresa_id', $empresaId)
            ->orderByDesc('fecha')
            ->paginate(20);
        return Inertia::render('Odontologia/Presupuestos/Index', compact('presupuestos'));
    }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required|exists:odonto_pacientes,id',
            'doctor_id'   => 'required|exists:odonto_doctores,id',
            'items'       => 'required|array|min:1',
            'items.*.descripcion' => 'required|string',
            'items.*.precio'      => 'required|numeric|min:0',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        $total = collect($request->items)->sum(fn($i) => $i['precio'] * $i['cantidad']);

        $presupuesto = OdontoPresupuesto::create([
            'empresa_id'   => $this->empresaId(),
            'paciente_id'  => $request->paciente_id,
            'doctor_id'    => $request->doctor_id,
            'fecha'        => now()->toDateString(),
            'estado'       => 'borrador',
            'total'        => $total,
            'observaciones'=> $request->observaciones,
        ]);

        foreach ($request->items as $item) {
            OdontoPresupuestoItem::create([
                'presupuesto_id' => $presupuesto->id,
                'tratamiento_id' => $item['tratamiento_id'] ?? null,
                'numero_pieza'   => $item['numero_pieza'] ?? null,
                'descripcion'    => $item['descripcion'],
                'precio'         => $item['precio'],
                'cantidad'       => $item['cantidad'],
                'subtotal'       => $item['precio'] * $item['cantidad'],
                'estado_item'    => 'pendiente',
            ]);
        }

        return back()->with('success', 'Presupuesto creado correctamente.');
    }

    public function update(Request $request, $id) {
        $presupuesto = OdontoPresupuesto::where('empresa_id', $this->empresaId())->findOrFail($id);
        $presupuesto->update($request->only(['estado','observaciones']));
        return back()->with('success', 'Presupuesto actualizado.');
    }

    public function catalogo() {
        $tratamientos = OdontoTratamientoCatalogo::where('empresa_id', $this->empresaId())
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();
        return response()->json($tratamientos);
    }

    public function storeCatalogo(Request $request) {
        $request->validate([
            'nombre'     => 'required|string|max:100',
            'precio_base'=> 'required|numeric|min:0',
        ]);
        OdontoTratamientoCatalogo::create([
            ...$request->only(['nombre','categoria','descripcion','precio_base']),
            'empresa_id' => $this->empresaId(),
        ]);
        return back()->with('success', 'Tratamiento agregado al catálogo.');
    }
}
