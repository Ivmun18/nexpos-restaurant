<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Lote;
use App\Helpers\EmpresaHelper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class FarmaciaProductosImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected $empresaId;
    protected $categoriasCache = [];
    protected $errores = [];
    protected $productosCreados = 0;
    protected $categoriasCreadas = 0;

    public function __construct()
    {
        $this->empresaId = EmpresaHelper::id();
    }

    public function model(array $row)
    {
        if (empty($row['codigo']) || empty($row['descripcion']) || !isset($row['precio_compra']) || !isset($row['precio_venta']) || !isset($row['stock'])) {
            $this->errores[] = "Fila con datos incompletos: " . json_encode($row);
            return null;
        }

        try {
            DB::beginTransaction();

            $categoriaId = null;
            if (!empty($row['categoria'])) {
                $categoriaId = $this->obtenerOCrearCategoria($row['categoria']);
            }

            $productoExistente = Producto::where('empresa_id', $this->empresaId)
                ->where('codigo', $row['codigo'])
                ->first();

            if ($productoExistente) {
                $this->errores[] = "Producto duplicado: {$row['codigo']} - {$row['descripcion']}";
                DB::rollBack();
                return null;
            }

            $producto = Producto::create([
                'empresa_id'        => $this->empresaId,
                'codigo'            => $row['codigo'],
                'codigo_barras'     => $row['codigo_barras'] ?? null,
                'descripcion'       => strtoupper($row['descripcion']),
                'categoria_id'      => $categoriaId,
                'precio_compra'     => (float) $row['precio_compra'],
                'precio_venta'      => (float) $row['precio_venta'],
                'stock'             => (int) $row['stock'],
                'stock_minimo'      => !empty($row['stock_minimo']) ? (int) $row['stock_minimo'] : 0,
                'unidad'            => $row['unidad'] ?? 'UND',
                'principio_activo'  => $row['principio_activo'] ?? null,
                'laboratorio'       => $row['laboratorio'] ?? null,
                'presentacion'      => $row['presentacion'] ?? null,
                'requiere_receta'   => $this->parsearSiNo($row['requiere_receta'] ?? 'NO'),
                'afecto_igv'        => $this->parsearSiNo($row['afecto_igv'] ?? 'NO'),
                'activo'            => true,
            ]);

            if (!empty($row['lote']) && !empty($row['fecha_vencimiento'])) {
                Lote::create([
                    'empresa_id'       => $this->empresaId,
                    'producto_id'      => $producto->id,
                    'lote'             => $row['lote'],
                    'fecha_vencimiento' => $this->parsearFecha($row['fecha_vencimiento']),
                    'stock'            => (int) $row['stock'],
                ]);
            }

            $this->productosCreados++;
            DB::commit();

            return $producto;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errores[] = "Error al importar {$row['codigo']}: " . $e->getMessage();
            return null;
        }
    }

    protected function obtenerOCrearCategoria($nombreCategoria)
    {
        $nombreCategoria = strtoupper(trim($nombreCategoria));

        if (isset($this->categoriasCache[$nombreCategoria])) {
            return $this->categoriasCache[$nombreCategoria];
        }

        $categoria = Categoria::where('empresa_id', $this->empresaId)
            ->where('nombre', $nombreCategoria)
            ->first();

        if (!$categoria) {
            $categoria = Categoria::create([
                'empresa_id' => $this->empresaId,
                'nombre'     => $nombreCategoria,
                'activo'     => true,
            ]);
            $this->categoriasCreadas++;
        }

        $this->categoriasCache[$nombreCategoria] = $categoria->id;

        return $categoria->id;
    }

    protected function parsearSiNo($valor)
    {
        if (empty($valor)) return false;
        $valor = strtoupper(trim($valor));
        return in_array($valor, ['SI', 'SÍ', 'YES', 'S', 'Y', '1', 'TRUE']);
    }

    protected function parsearFecha($fecha)
    {
        if (empty($fecha)) return null;

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                return $fecha;
            }

            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha)) {
                $partes = explode('/', $fecha);
                return "{$partes[2]}-{$partes[1]}-{$partes[0]}";
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'codigo'         => 'required|string|max:50',
            'descripcion'    => 'required|string|max:300',
            'precio_compra'  => 'required|numeric|min:0',
            'precio_venta'   => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
        ];
    }

    public function getEstadisticas()
    {
        return [
            'productos_creados'   => $this->productosCreados,
            'categorias_creadas'  => $this->categoriasCreadas,
            'errores'             => $this->errores,
            'total_errores'       => count($this->errores),
        ];
    }
}
