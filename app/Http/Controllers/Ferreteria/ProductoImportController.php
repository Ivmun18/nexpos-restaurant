<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FerreteriaProductosImport;

class ProductoImportController extends Controller
{
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $import = new FerreteriaProductosImport();
            Excel::import($import, $request->file('archivo'));
            
            $stats = $import->getEstadisticas();
            
            $mensaje = "✅ Importación completada: {$stats['productos_creados']} productos creados";
            
            if ($stats['categorias_creadas'] > 0) {
                $mensaje .= ", {$stats['categorias_creadas']} categorías nuevas";
            }
            
            if (!empty($stats['errores'])) {
                $mensaje .= ". " . count($stats['errores']) . " errores encontrados";
            }
            
            return redirect()->back()->with([
                'success' => $mensaje,
                'estadisticas' => $stats
            ]);
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errores = [];
            foreach ($failures as $failure) {
                $errores[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            return redirect()->back()->with([
                'error' => 'Error de validación en el archivo Excel',
                'errores' => $errores
            ]);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}
