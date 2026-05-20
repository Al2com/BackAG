<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('proveedores')->insert([
            [
                'nombre_empresa'   => 'Tienda Cooperativa',
                'direccion'        => 'Calle Mayor, 1, Valencia',
                'telefono'         => '962540509',
                'nombre_comercial' => 'Cooperativa Agrícola',
                'descripcion'      => 'Proveedor cooperativo de productos agrícolas y suministros para el campo.',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'nombre_empresa'   => 'Rafael Martínez SL',
                'direccion'        => 'Avenida del Puerto, 45, Valencia',
                'telefono'         => '962345678',
                'nombre_comercial' => 'Rafael Martínez',
                'descripcion'      => 'Empresa proveedora de materiales y servicios para explotaciones agrícolas.',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }
}