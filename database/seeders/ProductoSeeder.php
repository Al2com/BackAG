<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        Producto::create([
            'nombre' => 'Fungicida Azufre',
            'materia_activa' => 'Azufre 80%',
            'precio' => 25.50,
            'ubicacion' => 'Estante A-3',
            'stock_minimo' => 10,
            'stock_actual' => 50,
            'unidad' => 'kg'
        ]);

        Producto::create([
            'nombre' => 'Insecticida Clorpirifos',
            'materia_activa' => 'Clorpirifos 48%',
            'precio' => 45.00,
            'ubicacion' => 'Estante B-1',
            'stock_minimo' => 5,
            'stock_actual' => 25,
            'unidad' => 'L'
        ]);

        Producto::create([
            'nombre' => 'Herbicida Glifosato',
            'materia_activa' => 'Glifosato 36%',
            'precio' => 32.00,
            'ubicacion' => 'Estante B-2',
            'stock_minimo' => 8,
            'stock_actual' => 30,
            'unidad' => 'L'
        ]);

        Producto::create([
            'nombre' => 'Abono NPK',
            'materia_activa' => 'NPK 15-15-15',
            'precio' => 18.50,
            'ubicacion' => 'Almacén principal',
            'stock_minimo' => 20,
            'stock_actual' => 100,
            'unidad' => 'kg'
        ]);
    }
}
