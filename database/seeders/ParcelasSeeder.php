<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParcelasSeeder extends Seeder
{
    public function run(): void
    {
       $parcelas = [
    [
        'explotacion_id' => 1,
        'propietarios_id' => 1,
        'nombre' => 'Avionetes',
        'rol' => 'manta',
        'poligono' => 24,
        'parcela' => 118,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 5.65,
        'num_arboles' => 250,
        'fecha_plantacion' => '1994-01-01',
        'descripcion' => 'Avionetes',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 2,
        'propietarios_id' => 1,
        'nombre' => 'Paradis1',
        'rol' => 'goteo',
        'poligono' => 1,
        'parcela' => 27,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 2.35,
        'num_arboles' => 250,
        'fecha_plantacion' => '2016-01-01',
        'descripcion' => 'Paradis1',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 2,
        'propietarios_id' => 1,
        'nombre' => 'Paradis2',
        'rol' => 'goteo',
        'poligono' => 1,
        'parcela' => 26,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 2.56,
        'num_arboles' => 250,
        'fecha_plantacion' => '2016-01-01',
        'descripcion' => 'Paradis2',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 1,
        'propietarios_id' => 1,
        'nombre' => 'Campet',
        'rol' => 'manta',
        'poligono' => 24,
        'parcela' => 45,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 1.74,
        'num_arboles' => 80,
        'fecha_plantacion' => '1991-01-01',
        'descripcion' => 'Campet',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 1,
        'propietarios_id' => 1,
        'nombre' => 'Motor Satsuma',
        'rol' => 'manta',
        'poligono' => 24,
        'parcela' => 52,
        'variedad' => 'Satsuma',
        'dimension_hanegadas' => 12.15,
        'num_arboles' => 466,
        'fecha_plantacion' => '2012-01-01',
        'descripcion' => 'Motor',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 1,
        'propietarios_id' => 1,
        'nombre' => 'Motor Kaki',
        'rol' => 'manta',
        'poligono' => 24,
        'parcela' => 51,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 18.00,
        'num_arboles' => 800,
        'fecha_plantacion' => '2022-02-17',
        'descripcion' => 'Motor',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 1,
        'propietarios_id' => 1,
        'nombre' => 'Elena',
        'rol' => 'manta',
        'poligono' => 5,
        'parcela' => 41,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 3.55,
        'num_arboles' => 144,
        'fecha_plantacion' => '1995-01-01',
        'descripcion' => 'Elena',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 3,
        'propietarios_id' => 1,
        'nombre' => 'Tosalet1',
        'rol' => 'goteo',
        'poligono' => 24,
        'parcela' => 1041,
        'variedad' => 'Okitsu',
        'dimension_hanegadas' => 4.30,
        'num_arboles' => 120,
        'fecha_plantacion' => '2008-01-01',
        'descripcion' => 'Tosalet1',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 3,
        'propietarios_id' => 1,
        'nombre' => 'Tosalet2',           
        'rol' => 'goteo',
        'poligono' => 24,
        'parcela' => 1042,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 1.50,
        'num_arboles' => 40,
        'fecha_plantacion' => '1999-01-01',
        'descripcion' => 'Tosalet2',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 4,
        'propietarios_id' => 1,
        'nombre' => 'Evols1',
        'rol' => 'manta',
        'poligono' => 17,
        'parcela' => 9000,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 3.92,
        'num_arboles' => 81,
        'fecha_plantacion' => '1990-01-01',
        'descripcion' => 'Evols1',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 4,
        'propietarios_id' => 1,
        'nombre' => 'Navel',
        'rol' => 'manta',
        'poligono' => 17,
        'parcela' => 9001,
        'variedad' => 'Rojo brillante',
        'dimension_hanegadas' => 2.50,
        'num_arboles' => 80,
        'fecha_plantacion' => '1990-01-01',
        'descripcion' => 'Navel',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 5,
        'propietarios_id' => 1,
        'nombre' => 'Olivarons',
        'rol' => 'goteo',
        'poligono' => 25,
        'parcela' => 32,
        'variedad' => 'Rojo Brillante',
        'dimension_hanegadas' => 6.94,
        'num_arboles' => 140,
        'fecha_plantacion' => '2009-01-01',
        'descripcion' => 'Evols Carmen Oliver',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'explotacion_id' => 4,
        'propietarios_id' => 1,
        'nombre' => 'Caseta',
        'rol' => 'manta',
        'poligono' => 15,
        'parcela' => 39,
        'variedad' => 'New-Hall',
        'dimension_hanegadas' => 4.37,
        'num_arboles' => 133,
        'fecha_plantacion' => '1981-01-01',
        'descripcion' => 'Caseta',
        'created_at' => now(),
        'updated_at' => now()
    ],
];

        DB::table('parcelas')->insert($parcelas);
    }
}
