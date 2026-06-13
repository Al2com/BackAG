<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PropietariosSeeder extends Seeder
{
    public function run(): void
    {
        $alvaro  = User::where('email', 'alvaro@test.com')->first();
        $andres  = User::where('email', 'andres@test.com')->first();
        $miguel  = User::where('email', 'usuario2@test.com')->first();

        DB::table('propietarios')->insert([
            [
                'nombre'     => 'Álvaro Comenge Oliver',
                'dni'        => '12345678A',
                'telefono'   => '963 123 456',
                'admin_id'   => $alvaro->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre'     => 'Andrés Marín',
                'dni'        => '23456789B',
                'telefono'   => '963 234 567',
                'admin_id'   => $andres->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre'     => 'Miguel Ángel',
                'dni'        => '34567890C',
                'telefono'   => '963 345 678',
                'admin_id'   => $miguel->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}