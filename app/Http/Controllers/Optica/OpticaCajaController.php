<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaCaja;
use App\Models\OpticaCajaMovimiento;
use Inertia\Inertia;
use Carbon\Carbon;

class OpticaCajaController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $cajaAbierta = OpticaCaja::where('empresa_id',$empresa_id)->where('estado','abierta')->with('movimientos')->latest()->first();
        $historial = OpticaCaja::where('empresa_id',$empresa_id)->where('estado','cerrada')->latest()->take(10)->get();
        return Inertia::render('Optica/Caja/Index', compact('cajaAbierta','historial'));
    }

    public function abrir(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $abierta = OpticaCaja::where('empresa_id',$empresa_id)->where('estado','abierta')->exists();
        if ($abierta) return back()->withErrors(['error'=>'Ya hay una caja abierta.']);
        $data = $request->validate(['monto_inicial'=>'required|numeric|min:0']);
        OpticaCaja::create([
            'empresa_id'    => $empresa_id,
            'user_id'       => auth()->id(),
            'fecha'         => Carbon::today(),
            'monto_inicial' => $data['monto_inicial'],
            'estado'        => 'abierta',
            'abierta_en'    => now(),
        ]);
        return back()->with('success','Caja abierta.');
    }

    public function cerrar(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $caja = OpticaCaja::where('empresa_id',$empresa_id)->where('estado','abierta')->latest()->firstOrFail();
        $data = $request->validate(['monto_final'=>'required|numeric|min:0','observaciones'=>'nullable|string']);
        $caja->update([
            'monto_final'   => $data['monto_final'],
            'observaciones' => $data['observaciones'] ?? null,
            'estado'        => 'cerrada',
            'cerrada_en'    => now(),
        ]);
        return back()->with('success','Caja cerrada.');
    }

    public function movimiento(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $caja = OpticaCaja::where('empresa_id',$empresa_id)->where('estado','abierta')->latest()->firstOrFail();
        $data = $request->validate([
            'tipo'=>'required|in:ingreso,egreso',
            'concepto'=>'required|string',
            'monto'=>'required|numeric|min:0.01',
        ]);
        OpticaCajaMovimiento::create(array_merge($data,['caja_id'=>$caja->id,'empresa_id'=>$empresa_id]));
        if ($data['tipo'] === 'egreso') $caja->increment('total_egresos',$data['monto']);
        return back()->with('success','Movimiento registrado.');
    }
}
