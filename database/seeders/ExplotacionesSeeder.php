<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ExplotacionesSeeder extends Seeder
{
  public function run(): void
{
    $explotaciones = [
        [
            'nombre' => 'La casa del pi',
            'ubicacion' => "L'Alcudia",
            'descripcion' => 'Explotación principal de caqui y naranja',
            'user_id' => 1,
            'propietario_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nombre' => 'Tosalet',
            'ubicacion' => "L'Alcudia",
            'descripcion' => 'Explotación de caqui y naranja',
            'user_id' => 1,
            'propietario_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nombre' => 'Evols',
            'ubicacion' => "L'Alcudia",
            'descripcion' => 'Explotación de caqui y naranja',
            'user_id' => 1,
            'propietario_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nombre' => 'Paradis',
            'ubicacion' => 'Benimodo',
            'descripcion' => 'Explotación de caqui',
            'user_id' => 1,
            'propietario_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
    ];

    DB::table('explotaciones')->insert($explotaciones);
}
}
