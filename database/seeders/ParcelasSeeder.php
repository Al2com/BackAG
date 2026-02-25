<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ParcelasSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $variedades = [
            'Arbequina',
            'Picual',
            'Hojiblanca',
            'Cornicabra',
            'Empeltre',
            'Blanqueta',
            'Manzanilla',
            'Verdial',
            'Changlot Real',
            'Alfafara'
        ];

        for ($i = 0; $i < 20; $i++) {
            DB::table('parcelas')->insert([
                'explotacion_id' => rand(1, 10),                           
                'propietarios_id' => 1,
                'rol' => $faker->randomElement(['manta', 'goteo']),
                'poligono' => rand(1, 50),
                'parcela' =>rand(1, 200),
                'variedad' => $faker->randomElement($variedades),
                'dimension_hanegadas' => $faker->randomFloat(3, 0.100, 0.999),
                'num_arboles' => rand(50, 1000),
                'fecha_plantacion' => $faker->dateTimeBetween('-30 years', '-1 year'),
                'descripcion' => $faker->sentence(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
