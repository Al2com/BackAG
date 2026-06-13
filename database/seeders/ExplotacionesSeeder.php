<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ExplotacionesSeeder extends Seeder
{
    public function run(): void
    {
        $alvaro = User::where('email', 'alvaro@test.com')->first();

        $explotaciones = [
            [
                'nombre'         => 'Casa el Pi',
                'ubicacion'      => "L'Alcudia",
                'descripcion'    => 'Explotación principal de caqui',
                'admin_id'       => $alvaro->id,
                'propietario_id' => 1,
                'created_at'     => now(),
                'updated_at'     => now()
            ],
            [
                'nombre'         => 'Paraiso',
                'ubicacion'      => 'Benimodo',
                'descripcion'    => 'Explotación de caqui',
                'admin_id'       => $alvaro->id,
                'propietario_id' => 1,
                'created_at'     => now(),
                'updated_at'     => now()
            ],
            [
                'nombre'         => 'Tosalet',
                'ubicacion'      => "L'Alcudia",
                'descripcion'    => 'Explotación de caqui',
                'admin_id'       => $alvaro->id,
                'propietario_id' => 1,
                'created_at'     => now(),
                'updated_at'     => now()
            ],
            [
                'nombre'         => 'Evols',
                'ubicacion'      => "L'Alcudia",
                'descripcion'    => 'Explotación de caqui',
                'admin_id'       => $alvaro->id,
                'propietario_id' => 1,
                'created_at'     => now(),
                'updated_at'     => now()
            ],
            [
                'nombre'         => 'Olivarons',
                'ubicacion'      => "L'Alcudia",
                'descripcion'    => 'Explotación de caqui',
                'admin_id'       => $alvaro->id,
                'propietario_id' => 1,
                'created_at'     => now(),
                'updated_at'     => now()
            ],
        ];

        DB::table('explotaciones')->insert($explotaciones);
    }
}