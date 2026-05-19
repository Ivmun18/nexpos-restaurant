<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Imports\FarmaciaProductosImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductoImportController extends Controller
{
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo Excel',
            'archivo.mimes'    => 'El archivo debe ser formato .xlsx, .xls o .csv',
            'archivo.max'      => 'El archivo no debe superar los 10MB',
        ]);

        try {
            $import = new FarmaciaProductosImport();
            
            Excel::import($import, $request->file('archivo'));

            $stats = $import->getEstadisticas();

            $mensaje = "✅ Importación completada: {$stats['productos_creados']} productos creados";
            
            if ($stats['categorias_creadas'] > 0) {
                $mensaje .= ", {$stats['categorias_creadas']} categorías nuevas";
            }

            if ($stats['total_errores'] > 0) {
                $mensaje .= ". ⚠️ {$stats['total_errores']} filas con errores";
            }

            return back()->with([
                'success'      => $mensaje,
                'estadisticas' => $stats,
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errores = [];
            
            foreach ($failures as $failure) {
                $errores[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()->with([
                'error'   => 'Error de validación en el archivo Excel',
                'errores' => $errores,
            ])->withInput();

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage())->withInput();
        }
    }
}
