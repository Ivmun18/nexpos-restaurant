<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPedidoLaboratorio;
use App\Models\Odontologia\OdontoProveedor;
use App\Models\Odontologia\OdontoInsumo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LaboratorioController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $pedidos = OdontoPedidoLaboratorio::with(['proveedor','paciente','doctor'])
            ->where('empresa_id', $empresaId)
            ->orderByDesc('fecha_pedido')
            ->paginate(20);
        $proveedores = OdontoProveedor::where('empresa_id', $empresaId)->where('activo', true)->get();
        return Inertia::render('Odontologia/Laboratorio/Index', compact('pedidos','proveedores'));
    }

    public function storePedido(Request $request) {
        $request->validate([
            'proveedor_id'          => 'required|exists:odonto_proveedores,id',
            'paciente_id'           => 'required|exists:odonto_pacientes,id',
            'doctor_id'             => 'required|exists:odonto_doctores,id',
            'tipo_trabajo'          => 'required|string',
            'fecha_entrega_esperada'=> 'nullable|date',
        ]);
        OdontoPedidoLaboratorio::create([
            ...$request->only(['proveedor_id','paciente_id','doctor_id','tipo_trabajo','descripcion','color_protesis','costo','fecha_entrega_esperada','observaciones']),
            'empresa_id'   => $this->empresaId(),
            'fecha_pedido' => now()->toDateString(),
            'estado'       => 'pendiente',
        ]);
        return back()->with('success', 'Pedido registrado correctamente.');
    }

    public function updatePedido(Request $request, $id) {
        $pedido = OdontoPedidoLaboratorio::where('empresa_id', $this->empresaId())->findOrFail($id);
        $pedido->update($request->only(['estado','fecha_entrega_real','observaciones','costo']));
        return back()->with('success', 'Pedido actualizado.');
    }

    public function proveedores() {
        $empresaId = $this->empresaId();
        $proveedores = OdontoProveedor::where('empresa_id', $empresaId)->orderBy('nombre')->get();
        return Inertia::render('Odontologia/Proveedores/Index', compact('proveedores'));
    }

    public function storeProveedor(Request $request) {
        $request->validate(['nombre' => 'required|string|max:100']);
        OdontoProveedor::create([
            ...$request->only(['nombre','ruc','contacto','telefono','email','tipo','observaciones']),
            'empresa_id' => $this->empresaId(),
        ]);
        return back()->with('success', 'Proveedor registrado.');
    }

    public function insumos() {
        $empresaId = $this->empresaId();
        $insumos = OdontoInsumo::where('empresa_id', $empresaId)->orderBy('nombre')->get();
        return Inertia::render('Odontologia/Insumos/Index', compact('insumos'));
    }

    public function storeInsumo(Request $request) {
        $request->validate(['nombre' => 'required|string|max:100']);
        OdontoInsumo::create([
            ...$request->only(['nombre','categoria','unidad','stock_actual','stock_minimo','precio_unitario']),
            'empresa_id' => $this->empresaId(),
        ]);
        return back()->with('success', 'Insumo registrado.');
    }

    public function updateInsumo(Request $request, $id) {
        $insumo = OdontoInsumo::where('empresa_id', $this->empresaId())->findOrFail($id);
        $insumo->update($request->only(['nombre','categoria','unidad','stock_actual','stock_minimo','precio_unitario','activo']));
        return back()->with('success', 'Insumo actualizado.');
    }
}
