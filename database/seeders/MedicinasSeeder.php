<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Empresa;

class MedicinasSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener la primera empresa (o crear advertencia)
        $empresa = Empresa::first();

        if (!$empresa) {
            $this->command->error('❌ No hay empresas registradas. Crea una empresa primero.');
            return;
        }

        $empresaId = $empresa->id;
        $this->command->info("📦 Insertando medicinas para empresa: {$empresa->razon_social} (ID: {$empresaId})");

        $medicinas = [
            // ANALGÉSICOS
            ['codigo' => 'MED001', 'codigo_barras' => '7750395001030', 'descripcion' => 'PARACETAMOL 500MG TAB', 'precio_venta' => 0.50, 'precio_compra' => 0.20, 'stock_actual' => 200, 'stock_minimo' => 50],
            ['codigo' => 'MED002', 'codigo_barras' => '7750395001047', 'descripcion' => 'PARACETAMOL 1G TAB', 'precio_venta' => 1.00, 'precio_compra' => 0.40, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED003', 'codigo_barras' => '7750395001054', 'descripcion' => 'IBUPROFENO 400MG TAB', 'precio_venta' => 0.80, 'precio_compra' => 0.30, 'stock_actual' => 180, 'stock_minimo' => 40],
            ['codigo' => 'MED004', 'codigo_barras' => '7750395001061', 'descripcion' => 'IBUPROFENO 600MG TAB', 'precio_venta' => 1.20, 'precio_compra' => 0.50, 'stock_actual' => 120, 'stock_minimo' => 30],
            ['codigo' => 'MED005', 'codigo_barras' => '7750395001078', 'descripcion' => 'NAPROXENO 550MG TAB', 'precio_venta' => 1.50, 'precio_compra' => 0.60, 'stock_actual' => 100, 'stock_minimo' => 20],
            ['codigo' => 'MED006', 'codigo_barras' => '7750395001085', 'descripcion' => 'ASPIRINA 100MG TAB', 'precio_venta' => 0.40, 'precio_compra' => 0.15, 'stock_actual' => 250, 'stock_minimo' => 50],
            ['codigo' => 'MED007', 'codigo_barras' => '7750395001092', 'descripcion' => 'ASPIRINA 500MG TAB', 'precio_venta' => 0.60, 'precio_compra' => 0.25, 'stock_actual' => 200, 'stock_minimo' => 40],
            ['codigo' => 'MED008', 'codigo_barras' => '7750395001108', 'descripcion' => 'DICLOFENACO 50MG TAB', 'precio_venta' => 0.70, 'precio_compra' => 0.25, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED009', 'codigo_barras' => '7750395001115', 'descripcion' => 'DICLOFENACO 75MG AMP', 'precio_venta' => 3.50, 'precio_compra' => 1.50, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'MED010', 'codigo_barras' => '7750395001122', 'descripcion' => 'METAMIZOL 500MG TAB', 'precio_venta' => 0.50, 'precio_compra' => 0.20, 'stock_actual' => 180, 'stock_minimo' => 40],
            ['codigo' => 'MED011', 'codigo_barras' => '7750395001139', 'descripcion' => 'METAMIZOL 1G AMP', 'precio_venta' => 2.50, 'precio_compra' => 1.00, 'stock_actual' => 100, 'stock_minimo' => 25],
            ['codigo' => 'MED012', 'codigo_barras' => '7750395001146', 'descripcion' => 'KETOROLACO 10MG TAB', 'precio_venta' => 1.20, 'precio_compra' => 0.50, 'stock_actual' => 90, 'stock_minimo' => 20],

            // ANTIBIÓTICOS
            ['codigo' => 'MED013', 'codigo_barras' => '7750395002013', 'descripcion' => 'AMOXICILINA 500MG CAP', 'precio_venta' => 1.50, 'precio_compra' => 0.60, 'stock_actual' => 200, 'stock_minimo' => 50],
            ['codigo' => 'MED014', 'codigo_barras' => '7750395002020', 'descripcion' => 'AMOXICILINA 250MG/5ML SUSP', 'precio_venta' => 15.00, 'precio_compra' => 7.00, 'stock_actual' => 50, 'stock_minimo' => 15],
            ['codigo' => 'MED015', 'codigo_barras' => '7750395002037', 'descripcion' => 'AMOXICILINA + ACIDO CLAVULANICO 875MG TAB', 'precio_venta' => 4.50, 'precio_compra' => 2.00, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'MED016', 'codigo_barras' => '7750395002044', 'descripcion' => 'AZITROMICINA 500MG TAB', 'precio_venta' => 5.00, 'precio_compra' => 2.20, 'stock_actual' => 100, 'stock_minimo' => 25],
            ['codigo' => 'MED017', 'codigo_barras' => '7750395002051', 'descripcion' => 'CEFALEXINA 500MG CAP', 'precio_venta' => 1.80, 'precio_compra' => 0.80, 'stock_actual' => 120, 'stock_minimo' => 30],
            ['codigo' => 'MED018', 'codigo_barras' => '7750395002068', 'descripcion' => 'CIPROFLOXACINO 500MG TAB', 'precio_venta' => 1.50, 'precio_compra' => 0.60, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED019', 'codigo_barras' => '7750395002075', 'descripcion' => 'CLINDAMICINA 300MG CAP', 'precio_venta' => 2.50, 'precio_compra' => 1.00, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'MED020', 'codigo_barras' => '7750395002082', 'descripcion' => 'DICLOXACILINA 500MG CAP', 'precio_venta' => 1.20, 'precio_compra' => 0.50, 'stock_actual' => 100, 'stock_minimo' => 25],
            ['codigo' => 'MED021', 'codigo_barras' => '7750395002099', 'descripcion' => 'ERITROMICINA 500MG TAB', 'precio_venta' => 1.80, 'precio_compra' => 0.70, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'MED022', 'codigo_barras' => '7750395002105', 'descripcion' => 'METRONIDAZOL 500MG TAB', 'precio_venta' => 0.80, 'precio_compra' => 0.30, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED023', 'codigo_barras' => '7750395002112', 'descripcion' => 'TRIMETOPRIMA + SULFAMETOXAZOL 800MG TAB', 'precio_venta' => 1.00, 'precio_compra' => 0.40, 'stock_actual' => 120, 'stock_minimo' => 25],

            // ANTIGRIPALES
            ['codigo' => 'MED024', 'codigo_barras' => '7750395003010', 'descripcion' => 'PANADOL ANTIGRIPAL TAB', 'precio_venta' => 1.50, 'precio_compra' => 0.70, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED025', 'codigo_barras' => '7750395003027', 'descripcion' => 'PANADOL FORTE TAB', 'precio_venta' => 1.20, 'precio_compra' => 0.50, 'stock_actual' => 180, 'stock_minimo' => 40],
            ['codigo' => 'MED026', 'codigo_barras' => '7750395003034', 'descripcion' => 'NASTIZOL COMPOSITUM TAB', 'precio_venta' => 1.30, 'precio_compra' => 0.60, 'stock_actual' => 120, 'stock_minimo' => 30],
            ['codigo' => 'MED027', 'codigo_barras' => '7750395003041', 'descripcion' => 'TERMOFREN TAB', 'precio_venta' => 0.80, 'precio_compra' => 0.35, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED028', 'codigo_barras' => '7750395003058', 'descripcion' => 'AMBROXOL 30MG TAB', 'precio_venta' => 0.90, 'precio_compra' => 0.35, 'stock_actual' => 130, 'stock_minimo' => 30],
            ['codigo' => 'MED029', 'codigo_barras' => '7750395003065', 'descripcion' => 'AMBROXOL JARABE 120ML', 'precio_venta' => 12.00, 'precio_compra' => 5.00, 'stock_actual' => 60, 'stock_minimo' => 15],
            ['codigo' => 'MED030', 'codigo_barras' => '7750395003072', 'descripcion' => 'BROMHEXINA JARABE 120ML', 'precio_venta' => 10.00, 'precio_compra' => 4.00, 'stock_actual' => 50, 'stock_minimo' => 15],
            ['codigo' => 'MED031', 'codigo_barras' => '7750395003089', 'descripcion' => 'SALBUTAMOL INHALADOR', 'precio_venta' => 18.00, 'precio_compra' => 8.00, 'stock_actual' => 40, 'stock_minimo' => 10],
            ['codigo' => 'MED032', 'codigo_barras' => '7750395003096', 'descripcion' => 'LORATADINA 10MG TAB', 'precio_venta' => 0.60, 'precio_compra' => 0.25, 'stock_actual' => 200, 'stock_minimo' => 40],
            ['codigo' => 'MED033', 'codigo_barras' => '7750395003102', 'descripcion' => 'CETIRIZINA 10MG TAB', 'precio_venta' => 0.70, 'precio_compra' => 0.30, 'stock_actual' => 180, 'stock_minimo' => 40],
            ['codigo' => 'MED034', 'codigo_barras' => '7750395003119', 'descripcion' => 'CLORFENAMINA 4MG TAB', 'precio_venta' => 0.30, 'precio_compra' => 0.10, 'stock_actual' => 250, 'stock_minimo' => 50],

            // GÁSTRICOS
            ['codigo' => 'MED035', 'codigo_barras' => '7750395004017', 'descripcion' => 'OMEPRAZOL 20MG CAP', 'precio_venta' => 0.80, 'precio_compra' => 0.30, 'stock_actual' => 200, 'stock_minimo' => 50],
            ['codigo' => 'MED036', 'codigo_barras' => '7750395004024', 'descripcion' => 'RANITIDINA 150MG TAB', 'precio_venta' => 0.60, 'precio_compra' => 0.25, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED037', 'codigo_barras' => '7750395004031', 'descripcion' => 'HIDROXIDO DE ALUMINIO + MAGNESIO SUSP 360ML', 'precio_venta' => 15.00, 'precio_compra' => 6.50, 'stock_actual' => 50, 'stock_minimo' => 15],
            ['codigo' => 'MED038', 'codigo_barras' => '7750395004048', 'descripcion' => 'SAL DE ANDREWS SOBRE', 'precio_venta' => 1.00, 'precio_compra' => 0.40, 'stock_actual' => 200, 'stock_minimo' => 50],
            ['codigo' => 'MED039', 'codigo_barras' => '7750395004055', 'descripcion' => 'BUSCAPINA 10MG TAB', 'precio_venta' => 1.20, 'precio_compra' => 0.50, 'stock_actual' => 120, 'stock_minimo' => 30],
            ['codigo' => 'MED040', 'codigo_barras' => '7750395004062', 'descripcion' => 'BUSCAPINA COMPOSITUM TAB', 'precio_venta' => 1.80, 'precio_compra' => 0.80, 'stock_actual' => 100, 'stock_minimo' => 25],
            ['codigo' => 'MED041', 'codigo_barras' => '7750395004079', 'descripcion' => 'DIMENHIDRINATO 50MG TAB', 'precio_venta' => 0.80, 'precio_compra' => 0.30, 'stock_actual' => 120, 'stock_minimo' => 30],
            ['codigo' => 'MED042', 'codigo_barras' => '7750395004086', 'descripcion' => 'METOCLOPRAMIDA 10MG TAB', 'precio_venta' => 0.50, 'precio_compra' => 0.20, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED043', 'codigo_barras' => '7750395004093', 'descripcion' => 'LOPERAMIDA 2MG CAP', 'precio_venta' => 0.80, 'precio_compra' => 0.30, 'stock_actual' => 130, 'stock_minimo' => 30],
            ['codigo' => 'MED044', 'codigo_barras' => '7750395004109', 'descripcion' => 'SUERO ORAL FLO SOBRE', 'precio_venta' => 2.50, 'precio_compra' => 1.00, 'stock_actual' => 100, 'stock_minimo' => 25],

            // VITAMINAS
            ['codigo' => 'MED045', 'codigo_barras' => '7750395005014', 'descripcion' => 'VITAMINA C 1G TAB EFERVESCENTE', 'precio_venta' => 1.50, 'precio_compra' => 0.60, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED046', 'codigo_barras' => '7750395005021', 'descripcion' => 'VITAMINA C 500MG TAB', 'precio_venta' => 0.30, 'precio_compra' => 0.10, 'stock_actual' => 300, 'stock_minimo' => 60],
            ['codigo' => 'MED047', 'codigo_barras' => '7750395005038', 'descripcion' => 'COMPLEJO B TAB', 'precio_venta' => 0.50, 'precio_compra' => 0.20, 'stock_actual' => 200, 'stock_minimo' => 40],
            ['codigo' => 'MED048', 'codigo_barras' => '7750395005045', 'descripcion' => 'COMPLEJO B AMP', 'precio_venta' => 3.50, 'precio_compra' => 1.50, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'MED049', 'codigo_barras' => '7750395005052', 'descripcion' => 'CENTRUM ADULTO TAB', 'precio_venta' => 2.00, 'precio_compra' => 0.90, 'stock_actual' => 100, 'stock_minimo' => 25],
            ['codigo' => 'MED050', 'codigo_barras' => '7750395005069', 'descripcion' => 'SUPRADYN TAB', 'precio_venta' => 1.80, 'precio_compra' => 0.80, 'stock_actual' => 120, 'stock_minimo' => 30],
            ['codigo' => 'MED051', 'codigo_barras' => '7750395005076', 'descripcion' => 'CALCIO + VITAMINA D TAB', 'precio_venta' => 0.80, 'precio_compra' => 0.30, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED052', 'codigo_barras' => '7750395005083', 'descripcion' => 'SULFATO FERROSO 300MG TAB', 'precio_venta' => 0.40, 'precio_compra' => 0.15, 'stock_actual' => 200, 'stock_minimo' => 40],
            ['codigo' => 'MED053', 'codigo_barras' => '7750395005090', 'descripcion' => 'ACIDO FOLICO 5MG TAB', 'precio_venta' => 0.30, 'precio_compra' => 0.10, 'stock_actual' => 250, 'stock_minimo' => 50],

            // CARDIOVASCULARES
            ['codigo' => 'MED054', 'codigo_barras' => '7750395006011', 'descripcion' => 'ENALAPRIL 10MG TAB', 'precio_venta' => 0.40, 'precio_compra' => 0.15, 'stock_actual' => 200, 'stock_minimo' => 40],
            ['codigo' => 'MED055', 'codigo_barras' => '7750395006028', 'descripcion' => 'LOSARTAN 50MG TAB', 'precio_venta' => 0.60, 'precio_compra' => 0.25, 'stock_actual' => 180, 'stock_minimo' => 40],
            ['codigo' => 'MED056', 'codigo_barras' => '7750395006035', 'descripcion' => 'AMLODIPINO 5MG TAB', 'precio_venta' => 0.50, 'precio_compra' => 0.20, 'stock_actual' => 180, 'stock_minimo' => 40],
            ['codigo' => 'MED057', 'codigo_barras' => '7750395006042', 'descripcion' => 'ATENOLOL 50MG TAB', 'precio_venta' => 0.50, 'precio_compra' => 0.20, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'MED058', 'codigo_barras' => '7750395006059', 'descripcion' => 'METFORMINA 850MG TAB', 'precio_venta' => 0.50, 'precio_compra' => 0.20, 'stock_actual' => 250, 'stock_minimo' => 50],
            ['codigo' => 'MED059', 'codigo_barras' => '7750395006066', 'descripcion' => 'GLIBENCLAMIDA 5MG TAB', 'precio_venta' => 0.30, 'precio_compra' => 0.10, 'stock_actual' => 200, 'stock_minimo' => 40],
            ['codigo' => 'MED060', 'codigo_barras' => '7750395006073', 'descripcion' => 'ATORVASTATINA 20MG TAB', 'precio_venta' => 1.00, 'precio_compra' => 0.40, 'stock_actual' => 120, 'stock_minimo' => 30],

            // DERMATOLÓGICOS
            ['codigo' => 'MED061', 'codigo_barras' => '7750395007018', 'descripcion' => 'CLOTRIMAZOL CREMA 20G', 'precio_venta' => 6.00, 'precio_compra' => 2.50, 'stock_actual' => 60, 'stock_minimo' => 15],
            ['codigo' => 'MED062', 'codigo_barras' => '7750395007025', 'descripcion' => 'BETAMETASONA CREMA 30G', 'precio_venta' => 8.00, 'precio_compra' => 3.50, 'stock_actual' => 50, 'stock_minimo' => 15],
            ['codigo' => 'MED063', 'codigo_barras' => '7750395007032', 'descripcion' => 'DICLOFENACO GEL 60G', 'precio_venta' => 12.00, 'precio_compra' => 5.00, 'stock_actual' => 50, 'stock_minimo' => 15],
            ['codigo' => 'MED064', 'codigo_barras' => '7750395007049', 'descripcion' => 'MENTHOLATUM POMADA 30G', 'precio_venta' => 8.00, 'precio_compra' => 3.50, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'MED065', 'codigo_barras' => '7750395007056', 'descripcion' => 'VICK VAPORUB POMADA 50G', 'precio_venta' => 10.00, 'precio_compra' => 4.50, 'stock_actual' => 70, 'stock_minimo' => 20],
            ['codigo' => 'MED066', 'codigo_barras' => '7750395007063', 'descripcion' => 'CALAMINA LOCION 120ML', 'precio_venta' => 8.00, 'precio_compra' => 3.50, 'stock_actual' => 40, 'stock_minimo' => 10],

            // INSUMOS
            ['codigo' => 'INS001', 'codigo_barras' => '7750395008015', 'descripcion' => 'ALCOHOL 70 GRADOS 250ML', 'precio_venta' => 4.00, 'precio_compra' => 1.50, 'stock_actual' => 100, 'stock_minimo' => 25],
            ['codigo' => 'INS002', 'codigo_barras' => '7750395008022', 'descripcion' => 'ALCOHOL 96 GRADOS 250ML', 'precio_venta' => 5.00, 'precio_compra' => 2.00, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'INS003', 'codigo_barras' => '7750395008039', 'descripcion' => 'AGUA OXIGENADA 120ML', 'precio_venta' => 3.00, 'precio_compra' => 1.20, 'stock_actual' => 100, 'stock_minimo' => 25],
            ['codigo' => 'INS004', 'codigo_barras' => '7750395008046', 'descripcion' => 'GASA ESTERIL 10X10 SOBRE', 'precio_venta' => 1.50, 'precio_compra' => 0.60, 'stock_actual' => 150, 'stock_minimo' => 30],
            ['codigo' => 'INS005', 'codigo_barras' => '7750395008053', 'descripcion' => 'ALGODON 100G', 'precio_venta' => 3.50, 'precio_compra' => 1.50, 'stock_actual' => 80, 'stock_minimo' => 20],
            ['codigo' => 'INS006', 'codigo_barras' => '7750395008060', 'descripcion' => 'CURITAS CAJA X 100', 'precio_venta' => 8.00, 'precio_compra' => 3.50, 'stock_actual' => 50, 'stock_minimo' => 15],
            ['codigo' => 'INS007', 'codigo_barras' => '7750395008077', 'descripcion' => 'JERINGA 5ML DESCARTABLE', 'precio_venta' => 0.80, 'precio_compra' => 0.30, 'stock_actual' => 300, 'stock_minimo' => 60],
            ['codigo' => 'INS008', 'codigo_barras' => '7750395008084', 'descripcion' => 'JERINGA 10ML DESCARTABLE', 'precio_venta' => 1.00, 'precio_compra' => 0.40, 'stock_actual' => 250, 'stock_minimo' => 50],
            ['codigo' => 'INS009', 'codigo_barras' => '7750395008091', 'descripcion' => 'MASCARILLA QUIRURGICA UND', 'precio_venta' => 0.50, 'precio_compra' => 0.15, 'stock_actual' => 500, 'stock_minimo' => 100],
            ['codigo' => 'INS010', 'codigo_barras' => '7750395008107', 'descripcion' => 'GUANTES LATEX TALLA M PAR', 'precio_venta' => 1.00, 'precio_compra' => 0.40, 'stock_actual' => 200, 'stock_minimo' => 50],
            ['codigo' => 'INS011', 'codigo_barras' => '7750395008114', 'descripcion' => 'TERMOMETRO DIGITAL', 'precio_venta' => 25.00, 'precio_compra' => 12.00, 'stock_actual' => 30, 'stock_minimo' => 10],
            ['codigo' => 'INS012', 'codigo_barras' => '7750395008121', 'descripcion' => 'TENSIOMETRO DIGITAL BRAZO', 'precio_venta' => 120.00, 'precio_compra' => 70.00, 'stock_actual' => 15, 'stock_minimo' => 5],
        ];

        $insertadas = 0;
        $actualizadas = 0;

        foreach ($medicinas as $med) {
            $existe = Producto::where('empresa_id', $empresaId)
                ->where('codigo', $med['codigo'])
                ->exists();

            Producto::updateOrCreate(
                ['empresa_id' => $empresaId, 'codigo' => $med['codigo']],
                [
                    'codigo_barras'       => $med['codigo_barras'],
                    'descripcion'         => $med['descripcion'],
                    'tipo'                => 'producto',
                    'unidad_medida'       => 'NIU',
                    'precio_venta'        => $med['precio_venta'],
                    'precio_compra'       => $med['precio_compra'],
                    'tipo_afectacion_igv' => '10',
                    'afecto_igv'          => 1,
                    'controla_stock'      => 1,
                    'stock_actual'        => $med['stock_actual'],
                    'stock_minimo'        => $med['stock_minimo'],
                    'activo'              => 1,
                ]
            );

            $existe ? $actualizadas++ : $insertadas++;
        }

        $this->command->info("✅ {$insertadas} medicinas insertadas | 🔄 {$actualizadas} actualizadas");
    }
}
