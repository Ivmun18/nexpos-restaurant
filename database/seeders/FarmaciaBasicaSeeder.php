<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\CategoriaMinimarket;

/**
 * Plantilla básica de Farmacia — 100 medicamentos comunes en Perú
 * Categorías: 20 | Productos: 100
 */
class FarmaciaBasicaSeeder extends Seeder
{
    public function run($empresaId = null): void
    {
        if (!$empresaId) {
            throw new \Exception('FarmaciaBasicaSeeder requiere $empresaId como parámetro');
        }

        // ===== 1. Crear categorías =====
        $categoriasMap = [
            12 => 'Analgesicos',
            14 => 'Antibioticos',
            16 => 'Antiinflamatorios',
            17 => 'Antigripales',
            18 => 'Antialergicos',
            19 => 'Antiespasmodicos',
            20 => 'Antiparasitarios',
            21 => 'Digestivos',
            22 => 'Antiacidos',
            23 => 'Antidiarreicos',
            27 => 'Anticonceptivos',
            31 => 'Vitaminas',
            32 => 'Suplementos',
            34 => 'Electrolitos',
            39 => 'Oftalmicos',
            42 => 'Respiratorios',
            43 => 'Cardiovasculares',
            44 => 'Hipertension',
            51 => 'Dermatologicos',
            57 => 'Curaciones',
        ];

        $catIdReal = [];
        foreach ($categoriasMap as $idOld => $nombre) {
            $cat = CategoriaMinimarket::firstOrCreate(
                ['empresa_id' => $empresaId, 'nombre' => $nombre]
            );
            $catIdReal[$idOld] = $cat->id;
        }

        // ===== 2. Crear productos =====
        $productos = [
            ['desc' => 'Aciclovir crema 5% 5g', 'desc_corta' => 'Aciclovir crema 5% 5g', 'codigo' => '7750182069011', 'precio' => 12, 'stock_min' => 5, 'cat_ref' => 51, 'lab' => 'GSK', 'pa' => 'Aciclovir', 'pres' => 'Crema', 'conc' => '5%', 'receta' => false],
            ['desc' => 'Acido folico 5mg x 30', 'desc_corta' => 'Acido folico 5mg x 30', 'codigo' => '7750182062012', 'precio' => 0.8, 'stock_min' => 5, 'cat_ref' => 32, 'lab' => 'Hersil', 'pa' => 'Acido folico', 'pres' => 'Tabletas', 'conc' => '5mg', 'receta' => false],
            ['desc' => 'Agua oxigenada 250ml', 'desc_corta' => 'Agua oxigenada 250ml', 'codigo' => '7750182086018', 'precio' => 4, 'stock_min' => 5, 'cat_ref' => 57, 'lab' => 'Drokasa', 'pa' => 'Peroxido de hidrogeno', 'pres' => 'Liquido', 'conc' => '10vol', 'receta' => false],
            ['desc' => 'Albendazol 400mg x 1', 'desc_corta' => 'Albendazol 400mg x 1', 'codigo' => '7750182048016', 'precio' => 3, 'stock_min' => 5, 'cat_ref' => 20, 'lab' => 'Medifarma', 'pa' => 'Albendazol', 'pres' => 'Tableta', 'conc' => '400mg', 'receta' => false],
            ['desc' => 'Albendazol susp 100mg/5ml', 'desc_corta' => 'Albendazol susp 100mg/5ml', 'codigo' => '7750182048023', 'precio' => 9, 'stock_min' => 5, 'cat_ref' => 20, 'lab' => 'Medifarma', 'pa' => 'Albendazol', 'pres' => 'Suspension', 'conc' => '20ml', 'receta' => false],
            ['desc' => 'Alcohol 70 grados 250ml', 'desc_corta' => 'Alcohol 70 grados 250ml', 'codigo' => '7750182085011', 'precio' => 5.5, 'stock_min' => 5, 'cat_ref' => 57, 'lab' => 'Drokasa', 'pa' => 'Etanol', 'pres' => 'Liquido', 'conc' => '70%', 'receta' => false],
            ['desc' => 'Ambroxol 30mg x 30', 'desc_corta' => 'Ambroxol 30mg x 30', 'codigo' => '7750182027028', 'precio' => 2.5, 'stock_min' => 5, 'cat_ref' => 42, 'lab' => 'Hersil', 'pa' => 'Ambroxol', 'pres' => 'Tabletas', 'conc' => '30mg', 'receta' => false],
            ['desc' => 'Ambroxol jarabe 15mg/5ml', 'desc_corta' => 'Ambroxol jarabe 15mg/5ml', 'codigo' => '7750182027011', 'precio' => 8.5, 'stock_min' => 5, 'cat_ref' => 42, 'lab' => 'Medifarma', 'pa' => 'Ambroxol', 'pres' => 'Jarabe', 'conc' => '120ml', 'receta' => false],
            ['desc' => 'Amlodipino 5mg x 30', 'desc_corta' => 'Amlodipino 5mg x 30', 'codigo' => '7750182080016', 'precio' => 1.6, 'stock_min' => 5, 'cat_ref' => 44, 'lab' => 'Genfar', 'pa' => 'Amlodipino', 'pres' => 'Tabletas', 'conc' => '5mg', 'receta' => true],
            ['desc' => 'Amoxicilina 500mg x 100', 'desc_corta' => 'Amoxicilina 500mg x 100', 'codigo' => '7750182010013', 'precio' => 2.5, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Genfar', 'pa' => 'Amoxicilina', 'pres' => 'Capsulas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Amoxicilina susp 250mg/5ml', 'desc_corta' => 'Amoxicilina susp 250mg/5ml', 'codigo' => '7750182010020', 'precio' => 12, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Medifarma', 'pa' => 'Amoxicilina', 'pres' => 'Suspension', 'conc' => '250mg/5ml', 'receta' => true],
            ['desc' => 'Ampicilina 500mg x 100', 'desc_corta' => 'Ampicilina 500mg x 100', 'codigo' => '7750182019016', 'precio' => 1.8, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Medifarma', 'pa' => 'Ampicilina', 'pres' => 'Capsulas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Apronax 550mg x 30', 'desc_corta' => 'Apronax 550mg x 30', 'codigo' => '7750182009017', 'precio' => 3.8, 'stock_min' => 5, 'cat_ref' => 16, 'lab' => 'Bayer', 'pa' => 'Naproxeno sodico', 'pres' => 'Tabletas', 'conc' => '550mg', 'receta' => false],
            ['desc' => 'Aspirina 500mg x 100', 'desc_corta' => 'Aspirina 500mg x 100', 'codigo' => '7750182005019', 'precio' => 1.3, 'stock_min' => 5, 'cat_ref' => 12, 'lab' => 'Bayer', 'pa' => 'Acido acetilsalicilico', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => false],
            ['desc' => 'Atenolol 50mg x 30', 'desc_corta' => 'Atenolol 50mg x 30', 'codigo' => '7750182078013', 'precio' => 1.8, 'stock_min' => 5, 'cat_ref' => 44, 'lab' => 'Markos', 'pa' => 'Atenolol', 'pres' => 'Tabletas', 'conc' => '50mg', 'receta' => true],
            ['desc' => 'Atorvastatina 20mg x 30', 'desc_corta' => 'Atorvastatina 20mg x 30', 'codigo' => '7750182079010', 'precio' => 3.8, 'stock_min' => 5, 'cat_ref' => 43, 'lab' => 'Pfizer', 'pa' => 'Atorvastatina', 'pres' => 'Tabletas', 'conc' => '20mg', 'receta' => true],
            ['desc' => 'Azitromicina 500mg x 3', 'desc_corta' => 'Azitromicina 500mg x 3', 'codigo' => '7750182011010', 'precio' => 9, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Pfizer', 'pa' => 'Azitromicina', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Bromhexina jarabe 120ml', 'desc_corta' => 'Bromhexina jarabe 120ml', 'codigo' => '7750182028018', 'precio' => 7, 'stock_min' => 5, 'cat_ref' => 42, 'lab' => 'Markos', 'pa' => 'Bromhexina', 'pres' => 'Jarabe', 'conc' => '4mg/5ml', 'receta' => false],
            ['desc' => 'Buscapina compositum x 30', 'desc_corta' => 'Buscapina compositum x 30', 'codigo' => '7750182053010', 'precio' => 3.2, 'stock_min' => 5, 'cat_ref' => 19, 'lab' => 'Bayer', 'pa' => 'Butilhioscina+Dipirona', 'pres' => 'Tabletas', 'conc' => '10mg+250mg', 'receta' => false],
            ['desc' => 'Buscapina simple 10mg x 30', 'desc_corta' => 'Buscapina simple 10mg x 30', 'codigo' => '7750182053027', 'precio' => 2.5, 'stock_min' => 5, 'cat_ref' => 19, 'lab' => 'Bayer', 'pa' => 'Butilhioscina', 'pres' => 'Tabletas', 'conc' => '10mg', 'receta' => false],
            ['desc' => 'Calcio+Vit D 600mg x 60', 'desc_corta' => 'Calcio+Vit D 600mg x 60', 'codigo' => '7750182061015', 'precio' => 6.5, 'stock_min' => 5, 'cat_ref' => 32, 'lab' => 'Markos', 'pa' => 'Carbonato Ca+D3', 'pres' => 'Tabletas', 'conc' => '600mg+400UI', 'receta' => false],
            ['desc' => 'Cefalexina 500mg x 30', 'desc_corta' => 'Cefalexina 500mg x 30', 'codigo' => '7750182013014', 'precio' => 3.8, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Farmindustria', 'pa' => 'Cefalexina', 'pres' => 'Capsulas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Ceftriaxona 1g amp', 'desc_corta' => 'Ceftriaxona 1g amp', 'codigo' => '7750182023013', 'precio' => 12, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Genfar', 'pa' => 'Ceftriaxona', 'pres' => 'Inyectable', 'conc' => '1g', 'receta' => true],
            ['desc' => 'Centrum Adultos x 30', 'desc_corta' => 'Centrum Adultos x 30', 'codigo' => '7750182057018', 'precio' => 7.5, 'stock_min' => 5, 'cat_ref' => 31, 'lab' => 'Pfizer', 'pa' => 'Multivitaminico', 'pres' => 'Tabletas', 'conc' => 'estandar', 'receta' => false],
            ['desc' => 'Cetirizina 10mg x 30', 'desc_corta' => 'Cetirizina 10mg x 30', 'codigo' => '7750182042014', 'precio' => 1.6, 'stock_min' => 5, 'cat_ref' => 18, 'lab' => 'Hersil', 'pa' => 'Cetirizina', 'pres' => 'Tabletas', 'conc' => '10mg', 'receta' => false],
            ['desc' => 'Cicaplast Baume B5 40ml', 'desc_corta' => 'Cicaplast Baume B5 40ml', 'codigo' => '7750182066010', 'precio' => 35, 'stock_min' => 5, 'cat_ref' => 51, 'lab' => 'La Roche', 'pa' => 'Pantenol', 'pres' => 'Crema', 'conc' => '5%', 'receta' => false],
            ['desc' => 'Ciprofloxacino 500mg x 10', 'desc_corta' => 'Ciprofloxacino 500mg x 10', 'codigo' => '7750182012017', 'precio' => 4.5, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Hersil', 'pa' => 'Ciprofloxacino', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Clindamicina 300mg x 30', 'desc_corta' => 'Clindamicina 300mg x 30', 'codigo' => '7750182015018', 'precio' => 5.5, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Pfizer', 'pa' => 'Clindamicina', 'pres' => 'Capsulas', 'conc' => '300mg', 'receta' => true],
            ['desc' => 'Clorfenamina 4mg x 100', 'desc_corta' => 'Clorfenamina 4mg x 100', 'codigo' => '7750182043011', 'precio' => 0.5, 'stock_min' => 5, 'cat_ref' => 18, 'lab' => 'Markos', 'pa' => 'Clorfenamina', 'pres' => 'Tabletas', 'conc' => '4mg', 'receta' => false],
            ['desc' => 'Clotrimazol crema 1% 20g', 'desc_corta' => 'Clotrimazol crema 1% 20g', 'codigo' => '7750182068014', 'precio' => 8.5, 'stock_min' => 5, 'cat_ref' => 51, 'lab' => 'Bayer', 'pa' => 'Clotrimazol', 'pres' => 'Crema', 'conc' => '1%', 'receta' => false],
            ['desc' => 'Complejo B x 30', 'desc_corta' => 'Complejo B x 30', 'codigo' => '7750182059012', 'precio' => 2.8, 'stock_min' => 5, 'cat_ref' => 31, 'lab' => 'Genfar', 'pa' => 'Vitaminas B1+B6+B12', 'pres' => 'Tabletas', 'conc' => 'estandar', 'receta' => false],
            ['desc' => 'Cotrimoxazol 800+160mg x 30', 'desc_corta' => 'Cotrimoxazol 800+160mg x 30', 'codigo' => '7750182017012', 'precio' => 2.2, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Hersil', 'pa' => 'Cotrimoxazol', 'pres' => 'Tabletas', 'conc' => '800/160mg', 'receta' => true],
            ['desc' => 'Desloratadina 5mg x 30', 'desc_corta' => 'Desloratadina 5mg x 30', 'codigo' => '7750182045015', 'precio' => 4.5, 'stock_min' => 5, 'cat_ref' => 18, 'lab' => 'Pfizer', 'pa' => 'Desloratadina', 'pres' => 'Tabletas', 'conc' => '5mg', 'receta' => false],
            ['desc' => 'Dextrometorfano jarabe', 'desc_corta' => 'Dextrometorfano jarabe', 'codigo' => '7750182030011', 'precio' => 9, 'stock_min' => 5, 'cat_ref' => 42, 'lab' => 'Genfar', 'pa' => 'Dextrometorfano', 'pres' => 'Jarabe', 'conc' => '15mg/5ml', 'receta' => false],
            ['desc' => 'Diclofenaco 50mg x 100', 'desc_corta' => 'Diclofenaco 50mg x 100', 'codigo' => '7750182004012', 'precio' => 1, 'stock_min' => 5, 'cat_ref' => 16, 'lab' => 'Farmindustria', 'pa' => 'Diclofenaco', 'pres' => 'Tabletas', 'conc' => '50mg', 'receta' => false],
            ['desc' => 'Diclofenaco gel 60g', 'desc_corta' => 'Diclofenaco gel 60g', 'codigo' => '7750182004029', 'precio' => 15.5, 'stock_min' => 5, 'cat_ref' => 16, 'lab' => 'Bayer', 'pa' => 'Diclofenaco', 'pres' => 'Gel topico', 'conc' => '1%', 'receta' => false],
            ['desc' => 'Difenhidramina 50mg x 30', 'desc_corta' => 'Difenhidramina 50mg x 30', 'codigo' => '7750182044018', 'precio' => 2.2, 'stock_min' => 5, 'cat_ref' => 18, 'lab' => 'Farmindustria', 'pa' => 'Difenhidramina', 'pres' => 'Capsulas', 'conc' => '50mg', 'receta' => false],
            ['desc' => 'Domperidona 10mg x 30', 'desc_corta' => 'Domperidona 10mg x 30', 'codigo' => '7750182039014', 'precio' => 3.2, 'stock_min' => 5, 'cat_ref' => 21, 'lab' => 'Genfar', 'pa' => 'Domperidona', 'pres' => 'Tabletas', 'conc' => '10mg', 'receta' => false],
            ['desc' => 'Doxiciclina 100mg x 30', 'desc_corta' => 'Doxiciclina 100mg x 30', 'codigo' => '7750182016015', 'precio' => 2.8, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Genfar', 'pa' => 'Doxiciclina', 'pres' => 'Capsulas', 'conc' => '100mg', 'receta' => true],
            ['desc' => 'Enalapril 10mg x 30', 'desc_corta' => 'Enalapril 10mg x 30', 'codigo' => '7750182076019', 'precio' => 1.4, 'stock_min' => 5, 'cat_ref' => 44, 'lab' => 'Genfar', 'pa' => 'Enalapril', 'pres' => 'Tabletas', 'conc' => '10mg', 'receta' => true],
            ['desc' => 'Enalapril 20mg x 30', 'desc_corta' => 'Enalapril 20mg x 30', 'codigo' => '7750182076026', 'precio' => 2, 'stock_min' => 5, 'cat_ref' => 44, 'lab' => 'Genfar', 'pa' => 'Enalapril', 'pres' => 'Tabletas', 'conc' => '20mg', 'receta' => true],
            ['desc' => 'Eritromicina 500mg x 30', 'desc_corta' => 'Eritromicina 500mg x 30', 'codigo' => '7750182014011', 'precio' => 3.2, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Markos', 'pa' => 'Eritromicina', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Esomeprazol 40mg x 14', 'desc_corta' => 'Esomeprazol 40mg x 14', 'codigo' => '7750182040010', 'precio' => 9, 'stock_min' => 5, 'cat_ref' => 22, 'lab' => 'Pfizer', 'pa' => 'Esomeprazol', 'pres' => 'Tabletas', 'conc' => '40mg', 'receta' => false],
            ['desc' => 'Fexofenadina 180mg x 30', 'desc_corta' => 'Fexofenadina 180mg x 30', 'codigo' => '7750182046012', 'precio' => 7, 'stock_min' => 5, 'cat_ref' => 18, 'lab' => 'Genfar', 'pa' => 'Fexofenadina', 'pres' => 'Tabletas', 'conc' => '180mg', 'receta' => false],
            ['desc' => 'Gasas esteriles 10x10 x 10', 'desc_corta' => 'Gasas esteriles 10x10 x 10', 'codigo' => '7750182088012', 'precio' => 4.5, 'stock_min' => 5, 'cat_ref' => 57, 'lab' => 'Drokasa', 'pa' => 'Gasa esteril', 'pres' => 'Sobre', 'conc' => '10x10cm', 'receta' => false],
            ['desc' => 'Gentamicina 80mg amp', 'desc_corta' => 'Gentamicina 80mg amp', 'codigo' => '7750182022016', 'precio' => 7, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Markos', 'pa' => 'Gentamicina', 'pres' => 'Inyectable', 'conc' => '80mg/2ml', 'receta' => true],
            ['desc' => 'Hidrocortisona crema 1% 15g', 'desc_corta' => 'Hidrocortisona crema 1% 15g', 'codigo' => '7750182067017', 'precio' => 7.5, 'stock_min' => 5, 'cat_ref' => 51, 'lab' => 'Medifarma', 'pa' => 'Hidrocortisona', 'pres' => 'Crema', 'conc' => '1%', 'receta' => true],
            ['desc' => 'Hidroxido Mg+Al suspension', 'desc_corta' => 'Hidroxido Mg+Al suspension', 'codigo' => '7750182035016', 'precio' => 10.5, 'stock_min' => 5, 'cat_ref' => 22, 'lab' => 'Markos', 'pa' => 'Hidroxido Mg+Al', 'pres' => 'Suspension', 'conc' => '360ml', 'receta' => false],
            ['desc' => 'Hierro+Acido folico x 30', 'desc_corta' => 'Hierro+Acido folico x 30', 'codigo' => '7750182060018', 'precio' => 2.2, 'stock_min' => 5, 'cat_ref' => 32, 'lab' => 'Farmindustria', 'pa' => 'Sulfato ferroso+Folato', 'pres' => 'Tabletas', 'conc' => '60mg+400mcg', 'receta' => false],
            ['desc' => 'Hioscina 10mg amp', 'desc_corta' => 'Hioscina 10mg amp', 'codigo' => '7750182054017', 'precio' => 5, 'stock_min' => 5, 'cat_ref' => 19, 'lab' => 'Markos', 'pa' => 'Hioscina', 'pres' => 'Inyectable', 'conc' => '20mg/1ml', 'receta' => true],
            ['desc' => 'Ibuprofeno 400mg x 100', 'desc_corta' => 'Ibuprofeno 400mg x 100', 'codigo' => '7750182002018', 'precio' => 1.5, 'stock_min' => 5, 'cat_ref' => 16, 'lab' => 'Medifarma', 'pa' => 'Ibuprofeno', 'pres' => 'Tabletas', 'conc' => '400mg', 'receta' => false],
            ['desc' => 'Ibuprofeno 600mg x 50', 'desc_corta' => 'Ibuprofeno 600mg x 50', 'codigo' => '7750182002025', 'precio' => 2, 'stock_min' => 5, 'cat_ref' => 16, 'lab' => 'Genfar', 'pa' => 'Ibuprofeno', 'pres' => 'Tabletas', 'conc' => '600mg', 'receta' => false],
            ['desc' => 'Ketoconazol shampoo 100ml', 'desc_corta' => 'Ketoconazol shampoo 100ml', 'codigo' => '7750182071014', 'precio' => 18, 'stock_min' => 5, 'cat_ref' => 51, 'lab' => 'Janssen', 'pa' => 'Ketoconazol', 'pres' => 'Shampoo', 'conc' => '2%', 'receta' => false],
            ['desc' => 'Ketorolaco 10mg x 30', 'desc_corta' => 'Ketorolaco 10mg x 30', 'codigo' => '7750182006016', 'precio' => 3.2, 'stock_min' => 5, 'cat_ref' => 12, 'lab' => 'Markos', 'pa' => 'Ketorolaco', 'pres' => 'Tabletas', 'conc' => '10mg', 'receta' => true],
            ['desc' => 'Lagrimas artificiales 15ml', 'desc_corta' => 'Lagrimas artificiales 15ml', 'codigo' => '7750182075012', 'precio' => 14, 'stock_min' => 5, 'cat_ref' => 39, 'lab' => 'Alcon', 'pa' => 'Carboximetilcelulosa', 'pres' => 'Gotas oftalmicas', 'conc' => '0.5%', 'receta' => false],
            ['desc' => 'Levofloxacino 500mg x 7', 'desc_corta' => 'Levofloxacino 500mg x 7', 'codigo' => '7750182020012', 'precio' => 11, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Pfizer', 'pa' => 'Levofloxacino', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Loperamida 2mg x 30', 'desc_corta' => 'Loperamida 2mg x 30', 'codigo' => '7750182037010', 'precio' => 1.8, 'stock_min' => 5, 'cat_ref' => 23, 'lab' => 'Pfizer', 'pa' => 'Loperamida', 'pres' => 'Capsulas', 'conc' => '2mg', 'receta' => false],
            ['desc' => 'Loratadina 10mg x 30', 'desc_corta' => 'Loratadina 10mg x 30', 'codigo' => '7750182041017', 'precio' => 1.4, 'stock_min' => 5, 'cat_ref' => 18, 'lab' => 'Genfar', 'pa' => 'Loratadina', 'pres' => 'Tabletas', 'conc' => '10mg', 'receta' => false],
            ['desc' => 'Loratadina jarabe 120ml', 'desc_corta' => 'Loratadina jarabe 120ml', 'codigo' => '7750182041024', 'precio' => 10, 'stock_min' => 5, 'cat_ref' => 18, 'lab' => 'Genfar', 'pa' => 'Loratadina', 'pres' => 'Jarabe', 'conc' => '5mg/5ml', 'receta' => false],
            ['desc' => 'Loratadina+Pseudoefedrina x 10', 'desc_corta' => 'Loratadina+Pseudoefedrina x 10', 'codigo' => '7750182031018', 'precio' => 6, 'stock_min' => 5, 'cat_ref' => 17, 'lab' => 'Pfizer', 'pa' => 'Loratadina+Pseudoefedrina', 'pres' => 'Tabletas', 'conc' => '5mg+120mg', 'receta' => false],
            ['desc' => 'Losartan 50mg x 30', 'desc_corta' => 'Losartan 50mg x 30', 'codigo' => '7750182077016', 'precio' => 2.8, 'stock_min' => 5, 'cat_ref' => 44, 'lab' => 'Hersil', 'pa' => 'Losartan', 'pres' => 'Tabletas', 'conc' => '50mg', 'receta' => true],
            ['desc' => 'Magnesio+B6 x 60', 'desc_corta' => 'Magnesio+B6 x 60', 'codigo' => '7750182065013', 'precio' => 7, 'stock_min' => 5, 'cat_ref' => 32, 'lab' => 'Pfizer', 'pa' => 'Magnesio+Piridoxina', 'pres' => 'Tabletas', 'conc' => '470mg+5mg', 'receta' => false],
            ['desc' => 'Mebendazol 100mg x 6', 'desc_corta' => 'Mebendazol 100mg x 6', 'codigo' => '7750182049013', 'precio' => 2.5, 'stock_min' => 5, 'cat_ref' => 20, 'lab' => 'Hersil', 'pa' => 'Mebendazol', 'pres' => 'Tabletas', 'conc' => '100mg', 'receta' => false],
            ['desc' => 'Metamizol 500mg x 100', 'desc_corta' => 'Metamizol 500mg x 100', 'codigo' => '7750182008010', 'precio' => 1.1, 'stock_min' => 5, 'cat_ref' => 12, 'lab' => 'Hersil', 'pa' => 'Metamizol sodico', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => false],
            ['desc' => 'Metronidazol 500mg x 30', 'desc_corta' => 'Metronidazol 500mg x 30', 'codigo' => '7750182021019', 'precio' => 2.5, 'stock_min' => 5, 'cat_ref' => 20, 'lab' => 'Hersil', 'pa' => 'Metronidazol', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Metronidazol susp 250mg/5ml', 'desc_corta' => 'Metronidazol susp 250mg/5ml', 'codigo' => '7750182050019', 'precio' => 10, 'stock_min' => 5, 'cat_ref' => 20, 'lab' => 'Farmindustria', 'pa' => 'Metronidazol', 'pres' => 'Suspension', 'conc' => '120ml', 'receta' => true],
            ['desc' => 'Microgynon x 21', 'desc_corta' => 'Microgynon x 21', 'codigo' => '7750182081013', 'precio' => 17, 'stock_min' => 5, 'cat_ref' => 27, 'lab' => 'Bayer', 'pa' => 'Levonorgestrel+Etinilestradiol', 'pres' => 'Tabletas', 'conc' => '0.15mg+0.03mg', 'receta' => true],
            ['desc' => 'Mupirocina 2% 15g', 'desc_corta' => 'Mupirocina 2% 15g', 'codigo' => '7750182070017', 'precio' => 16, 'stock_min' => 5, 'cat_ref' => 51, 'lab' => 'GSK', 'pa' => 'Mupirocina', 'pres' => 'Pomada', 'conc' => '2%', 'receta' => true],
            ['desc' => 'Naproxeno 550mg x 50', 'desc_corta' => 'Naproxeno 550mg x 50', 'codigo' => '7750182003015', 'precio' => 2.5, 'stock_min' => 5, 'cat_ref' => 16, 'lab' => 'Hersil', 'pa' => 'Naproxeno', 'pres' => 'Tabletas', 'conc' => '550mg', 'receta' => false],
            ['desc' => 'Nitazoxanida 500mg x 6', 'desc_corta' => 'Nitazoxanida 500mg x 6', 'codigo' => '7750182052013', 'precio' => 12, 'stock_min' => 5, 'cat_ref' => 20, 'lab' => 'Pfizer', 'pa' => 'Nitazoxanida', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Omega 3 1000mg x 60', 'desc_corta' => 'Omega 3 1000mg x 60', 'codigo' => '7750182063019', 'precio' => 12, 'stock_min' => 5, 'cat_ref' => 32, 'lab' => 'Centrum', 'pa' => 'EPA+DHA', 'pres' => 'Capsulas blandas', 'conc' => '1000mg', 'receta' => false],
            ['desc' => 'Omeprazol 20mg x 30', 'desc_corta' => 'Omeprazol 20mg x 30', 'codigo' => '7750182032015', 'precio' => 1.8, 'stock_min' => 5, 'cat_ref' => 22, 'lab' => 'Genfar', 'pa' => 'Omeprazol', 'pres' => 'Capsulas', 'conc' => '20mg', 'receta' => false],
            ['desc' => 'Omeprazol 40mg x 14', 'desc_corta' => 'Omeprazol 40mg x 14', 'codigo' => '7750182032022', 'precio' => 3.2, 'stock_min' => 5, 'cat_ref' => 22, 'lab' => 'Genfar', 'pa' => 'Omeprazol', 'pres' => 'Capsulas', 'conc' => '40mg', 'receta' => false],
            ['desc' => 'Panadol Antigripal x 12', 'desc_corta' => 'Panadol Antigripal x 12', 'codigo' => '7750182024010', 'precio' => 4.5, 'stock_min' => 5, 'cat_ref' => 17, 'lab' => 'GSK', 'pa' => 'Paracetamol+Fenilefrina', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => false],
            ['desc' => 'Paracetamol 1g x 50', 'desc_corta' => 'Paracetamol 1g x 50', 'codigo' => '7750182001028', 'precio' => 1.2, 'stock_min' => 5, 'cat_ref' => 12, 'lab' => 'Medifarma', 'pa' => 'Paracetamol', 'pres' => 'Tabletas', 'conc' => '1g', 'receta' => false],
            ['desc' => 'Paracetamol 500mg x 100', 'desc_corta' => 'Paracetamol 500mg x 100', 'codigo' => '7750182001011', 'precio' => 0.8, 'stock_min' => 5, 'cat_ref' => 12, 'lab' => 'Medifarma', 'pa' => 'Paracetamol', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => false],
            ['desc' => 'Pargeverina 10mg x 30', 'desc_corta' => 'Pargeverina 10mg x 30', 'codigo' => '7750182056011', 'precio' => 3.8, 'stock_min' => 5, 'cat_ref' => 19, 'lab' => 'Genfar', 'pa' => 'Pargeverina', 'pres' => 'Tabletas', 'conc' => '10mg', 'receta' => false],
            ['desc' => 'Penicilina G Benzatinica 1.2M', 'desc_corta' => 'Penicilina G Benzatinica 1.2M', 'codigo' => '7750182018019', 'precio' => 16, 'stock_min' => 5, 'cat_ref' => 14, 'lab' => 'Farmindustria', 'pa' => 'Penicilina G', 'pres' => 'Inyectable', 'conc' => '1.2M UI', 'receta' => true],
            ['desc' => 'Postday x 1', 'desc_corta' => 'Postday x 1', 'codigo' => '7750182083017', 'precio' => 22, 'stock_min' => 5, 'cat_ref' => 27, 'lab' => 'Lafrancol', 'pa' => 'Levonorgestrel', 'pres' => 'Tableta', 'conc' => '1.5mg', 'receta' => true],
            ['desc' => 'Postinor 2 x 2', 'desc_corta' => 'Postinor 2 x 2', 'codigo' => '7750182084014', 'precio' => 26, 'stock_min' => 5, 'cat_ref' => 27, 'lab' => 'Gedeon', 'pa' => 'Levonorgestrel', 'pres' => 'Tabletas', 'conc' => '0.75mg', 'receta' => true],
            ['desc' => 'Prednisona 5mg x 30', 'desc_corta' => 'Prednisona 5mg x 30', 'codigo' => '7750182047019', 'precio' => 1.8, 'stock_min' => 5, 'cat_ref' => 16, 'lab' => 'Hersil', 'pa' => 'Prednisona', 'pres' => 'Tabletas', 'conc' => '5mg', 'receta' => true],
            ['desc' => 'Ranitidina 150mg x 30', 'desc_corta' => 'Ranitidina 150mg x 30', 'codigo' => '7750182033012', 'precio' => 1.4, 'stock_min' => 5, 'cat_ref' => 22, 'lab' => 'Hersil', 'pa' => 'Ranitidina', 'pres' => 'Tabletas', 'conc' => '150mg', 'receta' => false],
            ['desc' => 'Refrianex Adulto jarabe', 'desc_corta' => 'Refrianex Adulto jarabe', 'codigo' => '7750182026021', 'precio' => 16, 'stock_min' => 5, 'cat_ref' => 17, 'lab' => 'Farmindustria', 'pa' => 'Paracetamol+Pseudoefedrina', 'pres' => 'Jarabe', 'conc' => '120ml', 'receta' => false],
            ['desc' => 'Refrianex Pediatrico jarabe', 'desc_corta' => 'Refrianex Pediatrico jarabe', 'codigo' => '7750182026014', 'precio' => 13.5, 'stock_min' => 5, 'cat_ref' => 17, 'lab' => 'Farmindustria', 'pa' => 'Paracetamol+Clorfeniramina', 'pres' => 'Jarabe', 'conc' => '120ml', 'receta' => false],
            ['desc' => 'Sal de Andrews sobre', 'desc_corta' => 'Sal de Andrews sobre', 'codigo' => '7750182034019', 'precio' => 0.8, 'stock_min' => 5, 'cat_ref' => 22, 'lab' => 'GSK', 'pa' => 'Bicarbonato', 'pres' => 'Polvo', 'conc' => '5g', 'receta' => false],
            ['desc' => 'Salbutamol inhalador', 'desc_corta' => 'Salbutamol inhalador', 'codigo' => '7750182029015', 'precio' => 17, 'stock_min' => 5, 'cat_ref' => 42, 'lab' => 'GSK', 'pa' => 'Salbutamol', 'pres' => 'Inhalador', 'conc' => '100mcg', 'receta' => false],
            ['desc' => 'Sales de rehidratacion oral', 'desc_corta' => 'Sales de rehidratacion oral', 'codigo' => '7750182038017', 'precio' => 2.5, 'stock_min' => 5, 'cat_ref' => 34, 'lab' => 'Medifarma', 'pa' => 'Electrolitos', 'pres' => 'Polvo sobre', 'conc' => '20.5g', 'receta' => false],
            ['desc' => 'Simeticona 80mg x 30', 'desc_corta' => 'Simeticona 80mg x 30', 'codigo' => '7750182036013', 'precio' => 2.2, 'stock_min' => 5, 'cat_ref' => 21, 'lab' => 'Farmindustria', 'pa' => 'Simeticona', 'pres' => 'Tabletas', 'conc' => '80mg', 'receta' => false],
            ['desc' => 'Suero fisiologico 10ml gotas', 'desc_corta' => 'Suero fisiologico 10ml gotas', 'codigo' => '7750182074015', 'precio' => 5, 'stock_min' => 5, 'cat_ref' => 39, 'lab' => 'Farmindustria', 'pa' => 'Cloruro de sodio', 'pres' => 'Gotas oftalmicas', 'conc' => '0.9%', 'receta' => false],
            ['desc' => 'Suero oral electrolitos 500ml', 'desc_corta' => 'Suero oral electrolitos 500ml', 'codigo' => '7750182087015', 'precio' => 6.5, 'stock_min' => 5, 'cat_ref' => 34, 'lab' => 'Medifarma', 'pa' => 'Electrolitos', 'pres' => 'Liquido', 'conc' => '500ml', 'receta' => false],
            ['desc' => 'Tabcin Forte x 12', 'desc_corta' => 'Tabcin Forte x 12', 'codigo' => '7750182025017', 'precio' => 5, 'stock_min' => 5, 'cat_ref' => 17, 'lab' => 'Bayer', 'pa' => 'Paracetamol+Clorfenamina', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => false],
            ['desc' => 'Tinidazol 500mg x 4', 'desc_corta' => 'Tinidazol 500mg x 4', 'codigo' => '7750182051016', 'precio' => 5, 'stock_min' => 5, 'cat_ref' => 20, 'lab' => 'Genfar', 'pa' => 'Tinidazol', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => true],
            ['desc' => 'Tobramicina gotas 5ml', 'desc_corta' => 'Tobramicina gotas 5ml', 'codigo' => '7750182073018', 'precio' => 11, 'stock_min' => 5, 'cat_ref' => 39, 'lab' => 'Alcon', 'pa' => 'Tobramicina', 'pres' => 'Gotas oftalmicas', 'conc' => '0.3%', 'receta' => true],
            ['desc' => 'Tramadol 50mg x 30', 'desc_corta' => 'Tramadol 50mg x 30', 'codigo' => '7750182007013', 'precio' => 4.8, 'stock_min' => 5, 'cat_ref' => 12, 'lab' => 'Farmindustria', 'pa' => 'Tramadol', 'pres' => 'Capsulas', 'conc' => '50mg', 'receta' => true],
            ['desc' => 'Trimebutina 200mg x 30', 'desc_corta' => 'Trimebutina 200mg x 30', 'codigo' => '7750182055014', 'precio' => 4.5, 'stock_min' => 5, 'cat_ref' => 19, 'lab' => 'Farmindustria', 'pa' => 'Trimebutina', 'pres' => 'Tabletas', 'conc' => '200mg', 'receta' => false],
            ['desc' => 'Visine gotas 15ml', 'desc_corta' => 'Visine gotas 15ml', 'codigo' => '7750182072011', 'precio' => 13, 'stock_min' => 5, 'cat_ref' => 39, 'lab' => 'Pfizer', 'pa' => 'Tetrahidrozolina', 'pres' => 'Gotas oftalmicas', 'conc' => '0.05%', 'receta' => false],
            ['desc' => 'Vitamina C 1g x 30', 'desc_corta' => 'Vitamina C 1g x 30', 'codigo' => '7750182058015', 'precio' => 3.2, 'stock_min' => 5, 'cat_ref' => 31, 'lab' => 'Medifarma', 'pa' => 'Acido ascorbico', 'pres' => 'Efervescentes', 'conc' => '1g', 'receta' => false],
            ['desc' => 'Vitamina C 500mg x 100', 'desc_corta' => 'Vitamina C 500mg x 100', 'codigo' => '7750182058022', 'precio' => 1, 'stock_min' => 5, 'cat_ref' => 31, 'lab' => 'Hersil', 'pa' => 'Acido ascorbico', 'pres' => 'Tabletas', 'conc' => '500mg', 'receta' => false],
            ['desc' => 'Yasmin x 21', 'desc_corta' => 'Yasmin x 21', 'codigo' => '7750182082010', 'precio' => 65, 'stock_min' => 5, 'cat_ref' => 27, 'lab' => 'Bayer', 'pa' => 'Drospirenona+Etinilestradiol', 'pres' => 'Tabletas', 'conc' => '3mg+0.03mg', 'receta' => true],
            ['desc' => 'Zinc 50mg x 30', 'desc_corta' => 'Zinc 50mg x 30', 'codigo' => '7750182064016', 'precio' => 4.5, 'stock_min' => 5, 'cat_ref' => 32, 'lab' => 'Genfar', 'pa' => 'Sulfato de zinc', 'pres' => 'Tabletas', 'conc' => '50mg', 'receta' => false],
        ];

        foreach ($productos as $p) {
            $catId = $catIdReal[$p['cat_ref']] ?? null;
            Producto::create([
                'empresa_id'       => $empresaId,
                'descripcion'      => $p['desc'],
                'descripcion_corta'=> $p['desc_corta'] ?: null,
                'codigo_barras'    => $p['codigo'] ?: null,
                'codigo_producto'  => 'P-' . str_pad((string)rand(1,99999), 5, '0', STR_PAD_LEFT),
                'precio_venta'     => $p['precio'],
                'precio_compra'    => round($p['precio'] * 0.7, 2),
                'stock_actual'     => 50,
                'stock_minimo'     => $p['stock_min'],
                'unidad_medida'    => 'NIU',
                'categoria_id'     => $catId,
                'laboratorio'      => $p['lab'] ?: null,
                'principio_activo' => $p['pa'] ?: null,
                'presentacion'     => $p['pres'] ?: null,
                'concentracion'    => $p['conc'] ?: null,
                'requiere_receta'  => $p['receta'],
                'fecha_vencimiento'=> now()->addYears(2),
                'lote'             => 'L-DEMO-001',
                'activo'           => true,
            ]);
        }

        if (isset($this->command)) {
            $this->command->info('✅ FarmaciaBasicaSeeder: ' . count($productos) . ' productos cargados en empresa ' . $empresaId);
        }
    }
}
