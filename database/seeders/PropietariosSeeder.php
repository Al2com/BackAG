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
        $invitado = User::where('email', 'invitado@test.com')->first();

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
                'nombre'     => 'Invitado',
                'dni'        => '23456789B',
                'telefono'   => '963 234 567',
                'admin_id'   => $invitado->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}