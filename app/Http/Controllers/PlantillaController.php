<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plantilla;
use App\Models\Producto;
use App\Models\CategoriaMinimarket;
use App\Models\Venta;
use App\Models\VentaDetalle;

class PlantillaController extends Controller
{
    /**
     * Lista plantillas activas (para que el frontend las muestre)
     */
    public function index(Request $request)
    {
        $query = Plantilla::activas();
        if ($request->industry_type) {
            $query->porIndustria($request->industry_type);
        }
        return response()->json($query->get());
    }

    /**
     * Carga una plantilla en la empresa del usuario logueado
     */
    public function cargar(Request $request)
    {
        $request->validate([
            'plantilla_id' => 'required|exists:plantillas,id',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $plantilla = Plantilla::findOrFail($request->plantilla_id);

        if (!$plantilla->seeder_class) {
            return response()->json(['error' => 'Esta plantilla no tiene seeder asociado'], 400);
        }

        $seederClass = "\\Database\\Seeders\\" . $plantilla->seeder_class;
        if (!class_exists($seederClass)) {
            return response()->json(['error' => "Seeder no encontrado: {$seederClass}"], 404);
        }

        try {
            $seeder = new $seederClass();
            $seeder->run($empresaId);
            return back()->with('success', "✅ Plantilla '{$plantilla->nombre}' cargada con éxito: {$plantilla->total_productos} productos y {$plantilla->total_categorias} categorías.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar plantilla: ' . $e->getMessage()]);
        }
    }

    /**
     * Limpia TODOS los datos demo de la empresa (productos, ventas, categorías, caja)
     * ¡PELIGROSO! Solo se debe usar antes de empezar a operar real.
     */
    public function limpiarDatos(Request $request)
    {
        $request->validate([
            'confirmacion' => 'required|in:SI_BORRAR_TODO',
        ]);

        $empresaId = auth()->user()->empresa_id;

        \DB::beginTransaction();
        try {
            // Borrar detalles de ventas
            $ventaIds = Venta::where('empresa_id', $empresaId)->pluck('id');
            VentaDetalle::whereIn('venta_id', $ventaIds)->delete();

            // Borrar ventas
            $ventasBorradas = Venta::where('empresa_id', $empresaId)->delete();

            // Borrar productos
            $productosBorrados = Producto::where('empresa_id', $empresaId)->delete();

            // Borrar categorías
            $categoriasBorradas = CategoriaMinimarket::where('empresa_id', $empresaId)->delete();

            // Cerrar cajas abiertas
            \App\Models\CajaMinimarket::where('empresa_id', $empresaId)->delete();

            \DB::commit();

            return back()->with('success', '✅ Datos eliminados: ' . $ventasBorradas . ' ventas, ' . $productosBorrados . ' productos, ' . $categoriasBorradas . ' categorías.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Error al limpiar: ' . $e->getMessage()]);
        }
    }
}
