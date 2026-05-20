<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\CategoriasMinimarket;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Illuminate\Support\Facades\Log;
use App\Helpers\EmpresaHelper;

class FerreteriaProductosImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    protected $estadisticas = [
        'productos_creados' => 0,
        'categorias_creadas' => 0,
        'errores' => []
    ];

    private $categoriasCache = [];

    public function model(array $row)
    {
        try {
            if (empty($row['codigo']) || empty($row['descripcion']) || 
                empty($row['precio_compra']) || empty($row['precio_venta']) || 
                !isset($row['stock'])) {
                throw new \Exception("Faltan campos obligatorios");
            }

            $existe = Producto::where('empresa_id', EmpresaHelper::id())
                             ->where('codigo', $row['codigo'])
                             ->exists();
            
            if ($existe) {
                throw new \Exception("El código {$row['codigo']} ya existe");
            }

            $categoria_id = null;
            if (!empty($row['categoria'])) {
                $categoria_id = $this->obtenerOCrearCategoria($row['categoria']);
            }

            $producto = Producto::create([
                'empresa_id' => EmpresaHelper::id(),
                'codigo' => $row['codigo'],
                'codigo_barras' => $row['codigo_barras'] ?? null,
                'descripcion' => $row['descripcion'],
                'categoria_id' => $categoria_id,
                'precio_compra' => (float) $row['precio_compra'],
                'precio_venta' => (float) $row['precio_venta'],
                'stock_actual' => (int) $row['stock'],
                'stock_minimo' => isset($row['stock_minimo']) ? (int) $row['stock_minimo'] : 5,
                'unidad' => $row['unidad'] ?? 'UND',
                'marca' => $row['marca'] ?? null,
                'modelo' => $row['modelo'] ?? null,
                'ubicacion' => $row['ubicacion'] ?? null,
                'garantia_meses' => isset($row['garantia_meses']) ? (int) $row['garantia_meses'] : null,
                'afecto_igv' => $this->parsearBooleano($row['afecto_igv'] ?? 'SI'),
            ]);

            $this->estadisticas['productos_creados']++;
            return $producto;

        } catch (\Exception $e) {
            $this->estadisticas['errores'][] = "Fila {$row['codigo']}: " . $e->getMessage();
            Log::error("Error importando producto: " . $e->getMessage());
            return null;
        }
    }

    private function obtenerOCrearCategoria($nombre)
    {
        if (isset($this->categoriasCache[$nombre])) {
            return $this->categoriasCache[$nombre];
        }

        $categoria = CategoriasMinimarket::firstOrCreate(
            [
                'empresa_id' => EmpresaHelper::id(),
                'nombre' => $nombre
            ],
            [
                'icono' => '🔧',
                'color' => '#3B82F6'
            ]
        );

        if ($categoria->wasRecentlyCreated) {
            $this->estadisticas['categorias_creadas']++;
        }

        $this->categoriasCache[$nombre] = $categoria->id;
        return $categoria->id;
    }

    private function parsearBooleano($valor)
    {
        if (is_bool($valor)) return $valor;
        $valor = strtoupper(trim($valor));
        return in_array($valor, ['SI', 'SÍ', 'YES', 'S', 'Y', '1', 'TRUE']);
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required',
            'descripcion' => 'required',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->estadisticas['errores'][] = $e->getMessage();
    }

    public function getEstadisticas()
    {
        return $this->estadisticas;
    }
}
