<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropietariosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('propietarios')->insert([
            'nombre' => 'Álvaro Comenge Oliver',
            'dni' => '12345678A',
            'telefono' => '963 123 456',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
