<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaProducto;
use App\Models\OpticaCategoria;
use Inertia\Inertia;

class OpticaProductosController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $q = $request->get('q');
        $categoria = $request->get('categoria');
        $productos = OpticaProducto::where('empresa_id',$empresa_id)
            ->when($q, fn($query) => $query->where(fn($s) =>
                $s->where('nombre','like',"%$q%")->orWhere('codigo','like',"%$q%")->orWhere('marca','like',"%$q%")
            ))
            ->when($categoria, fn($query) => $query->where('categoria',$categoria))
            ->orderBy('categoria')->orderBy('nombre')
            ->paginate(25)->withQueryString();
        \$categorias = OpticaCategoria::where('empresa_id',\$empresa_id)->where('activo',true)->orderBy('nombre')->get();
        return Inertia::render('Optica/Productos/Index', compact('productos','q','categoria','categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'=>'nullable|string|max:50',
            'nombre'=>'required|string|max:150',
            'categoria'=>'required|in:luna,montura,lente_contacto,solucion,accesorio,otro',
            'marca'=>'nullable|string|max:80',
            'precio_compra'=>'required|numeric|min:0',
            'precio_venta'=>'required|numeric|min:0',
            'stock'=>'required|integer|min:0',
            'stock_minimo'=>'nullable|integer|min:0',
            'unidad'=>'nullable|string|max:20',
        ]);
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['activo'] = true;
        OpticaProducto::create($data);
        return back()->with('success','Producto registrado.');
    }

    public function update(Request $request, $id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $producto = OpticaProducto::where('empresa_id',$empresa_id)->findOrFail($id);
        $data = $request->validate([
            'codigo'=>'nullable|string|max:50',
            'nombre'=>'required|string|max:150',
            'categoria'=>'required|in:luna,montura,lente_contacto,solucion,accesorio,otro',
            'marca'=>'nullable|string|max:80',
            'precio_compra'=>'required|numeric|min:0',
            'precio_venta'=>'required|numeric|min:0',
            'stock'=>'required|integer|min:0',
            'stock_minimo'=>'nullable|integer|min:0',
            'unidad'=>'nullable|string|max:20',
            'activo'=>'boolean',
        ]);
        $producto->update($data);
        return back()->with('success','Producto actualizado.');
    }

    public function destroy($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $producto = OpticaProducto::where('empresa_id',$empresa_id)->findOrFail($id);
        $producto->delete();
        return back()->with('success','Producto eliminado.');
    }
}
