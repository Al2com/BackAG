<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ExplotacionesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $prefijos = ['Finca', 'Huerta', 'Mas', 'Cortijo', 'Alquería', 'Granja', 'Olivar', 'Hacienda'];
        $sufijos = ['del Sol', 'Los Olivos', 'Santa Ana', 'San José', 'La Esperanza', 'El Roble', 'Las Palmeras', 'Monte Alto', 'del Valle', 'La Font'];

        for ($i = 0; $i < 10; $i++) {  
            DB::table('explotaciones')->insert([
                'nombre' => $faker->randomElement($prefijos) . ' ' . $faker->randomElement($sufijos),
                'ubicacion' => $faker->city() . ', ' . $faker->randomElement(['Valencia', 'Castellón', 'Alicante']),
                'descripcion' => $faker->paragraph(2),
                'user_id' => 1,
                'propietario_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
