<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConfiguracionController extends Controller {
    private function empresa() {
        return Empresa::findOrFail(auth()->user()->empresa_id);
    }

    public function index() {
        return Inertia::render('Odontologia/Configuracion/Index', [
            'empresa' => $this->empresa()
        ]);
    }

    public function update(Request $request) {
        $request->validate([
            'razon_social'     => 'required|string|max:200',
            'nombre_comercial' => 'nullable|string|max:200',
            'ruc'              => 'required|string|size:11',
            'direccion'        => 'nullable|string|max:300',
            'distrito'         => 'nullable|string|max:100',
            'provincia'        => 'nullable|string|max:100',
            'departamento'     => 'nullable|string|max:100',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:150',
            'serie_boleta'     => 'nullable|string|max:10',
            'serie_factura'    => 'nullable|string|max:10',
        ]);

        $this->empresa()->update($request->only([
            'razon_social','nombre_comercial','ruc','direccion',
            'distrito','provincia','departamento','telefono','email',
            'serie_boleta','serie_factura','regimen_tributario',
        ]));

        return back()->with('success', 'Configuración guardada correctamente');
    }

    public function uploadLogo(Request $request) {
        $request->validate(['logo' => 'required|image|max:2048']);
        $empresa = $this->empresa();
        $path = $request->file('logo')->store('logos', 'public');
        $empresa->update(['logo' => $path]);
        return back()->with('success', 'Logo actualizado');
    }
}
