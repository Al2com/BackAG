<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Producto;
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'nombre' => 'Sercadis',
                'materia_activa' => 'Fluxapyroxad 12.5%',
                'precio' => 85.00,
                'ubicacion' => 'Estante A-1',
                'stock_minimo' => 5,
                'stock_actual' => 20,
                'dosis_recomendada' => 0.4,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Ortiva',
                'materia_activa' => 'Azoxistrobina 25%',
                'precio' => 72.00,
                'ubicacion' => 'Estante A-2',
                'stock_minimo' => 5,
                'stock_actual' => 15,
                'dosis_recomendada' => 0.5,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Noble',
                'materia_activa' => 'Folpet 50%',
                'precio' => 38.00,
                'ubicacion' => 'Estante A-3',
                'stock_minimo' => 8,
                'stock_actual' => 30,
                'dosis_recomendada' => 1.5,
                'unidad' => 'kg'
            ],
            [
                'nombre' => 'Volquete',
                'materia_activa' => 'Captan 80%',
                'precio' => 42.00,
                'ubicacion' => 'Estante A-4',
                'stock_minimo' => 8,
                'stock_actual' => 25,
                'dosis_recomendada' => 1.2,
                'unidad' => 'kg'
            ],
            [
                'nombre' => 'Omite Top',
                'materia_activa' => 'Propargite 30% + Cyflumetofen 10%',
                'precio' => 95.00,
                'ubicacion' => 'Estante B-1',
                'stock_minimo' => 4,
                'stock_actual' => 12,
                'dosis_recomendada' => 0.6,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Karate',
                'materia_activa' => 'Lambda-cihalotrin 2.5%',
                'precio' => 55.00,
                'ubicacion' => 'Estante B-2',
                'stock_minimo' => 5,
                'stock_actual' => 20,
                'dosis_recomendada' => 0.3,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Piriproxifen',
                'materia_activa' => 'Piriproxifen 10%',
                'precio' => 68.00,
                'ubicacion' => 'Estante B-3',
                'stock_minimo' => 4,
                'stock_actual' => 15,
                'dosis_recomendada' => 0.5,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Fosetil A',
                'materia_activa' => 'Fosetil-Al 80%',
                'precio' => 29.00,
                'ubicacion' => 'Estante A-5',
                'stock_minimo' => 10,
                'stock_actual' => 40,
                'dosis_recomendada' => 2.5,
                'unidad' => 'kg'
            ],
            [
                'nombre' => 'Corrector MN',
                'materia_activa' => 'Manganeso quelado 6%',
                'precio' => 22.00,
                'ubicacion' => 'Estante C-1',
                'stock_minimo' => 6,
                'stock_actual' => 25,
                'dosis_recomendada' => 0.3,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Corrector Ca',
                'materia_activa' => 'Calcio quelado 15%',
                'precio' => 24.00,
                'ubicacion' => 'Estante C-2',
                'stock_minimo' => 6,
                'stock_actual' => 25,
                'dosis_recomendada' => 0.4,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Aminoácidos',
                'materia_activa' => 'Aminoácidos libres 40%',
                'precio' => 35.00,
                'ubicacion' => 'Estante C-3',
                'stock_minimo' => 6,
                'stock_actual' => 20,
                'dosis_recomendada' => 0.5,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Abono Amoniaco',
                'materia_activa' => 'Nitrógeno amoniacal 20%',
                'precio' => 15.00,
                'ubicacion' => 'Almacén principal',
                'stock_minimo' => 20,
                'stock_actual' => 120,
                'dosis_recomendada' => 0.200,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Abono 15-15-15',
                'materia_activa' => 'NPK 15-15-15',
                'precio' => 18.00,
                'ubicacion' => 'Almacén principal',
                'stock_minimo' => 25,
                'stock_actual' => 150,
                'dosis_recomendada' => 4,
                'unidad' => 'kg'
            ],
            [
                'nombre' => 'Abono Calcio',
                'materia_activa' => 'Calcio 18% + Boro 0.2%',
                'precio' => 20.00,
                'ubicacion' => 'Almacén principal',
                'stock_minimo' => 15,
                'stock_actual' => 80,
                'dosis_recomendada' => 2.0,
                'unidad' => 'kg'
            ],
            [
                'nombre' => 'Abono Fem',
                'materia_activa' => 'Ácidos húmicos y fúlvicos 12%',
                'precio' => 28.00,
                'ubicacion' => 'Almacén principal',
                'stock_minimo' => 10,
                'stock_actual' => 50,
                'dosis_recomendada' => 1.0,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Insecticida Glifosato',
                'materia_activa' => 'Glifosato 36%',
                'precio' => 32.00,
                'ubicacion' => 'Estante B-4',
                'stock_minimo' => 8,
                'stock_actual' => 35,
                'dosis_recomendada' => 0.200,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Insecticida U46',
                'materia_activa' => 'MCPA 50%',
                'precio' => 27.00,
                'ubicacion' => 'Estante B-5',
                'stock_minimo' => 6,
                'stock_actual' => 20,
                'dosis_recomendada' => 0.150,
                'unidad' => 'L'
            ],
            [
                'nombre' => 'Insecticida Goal',
                'materia_activa' => 'Oxifluorfén 24%',
                'precio' => 45.00,
                'ubicacion' => 'Estante B-6',
                'stock_minimo' => 5,
                'stock_actual' => 18,
                'dosis_recomendada' => 0.10,
                'unidad' => 'L'
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}