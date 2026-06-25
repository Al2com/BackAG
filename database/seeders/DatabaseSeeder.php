<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Orden obligatorio:
        // 1. UserSeeder         -> crea los admins (Álvaro, Andrés, Miguel, ...).
        //                          Tiene que ir primero: los demás resuelven el
        //                          admin_id a partir de estos usuarios.
        // 2. PropietariosSeeder -> propietarios por admin (Álvaro usa el id=1 en
        //                          explotaciones/parcelas).
        // 3. ExplotacionesSeeder-> SOLO Álvaro (DB::table, admin_id explícito).
        // 4. ParcelasSeeder     -> SOLO Álvaro (DB::table, admin_id explícito).
        // 5. ProductoSeeder     -> catálogo único para CADA admin (su admin_id).
        // 6. ProveedoresSeeder  -> proveedores para CADA admin (su admin_id).
        //
        // ProductoCooperativaSeeder y ProductoCatalogoCanso2025Seeder quedan
        // FUERA a propósito: el catálogo válido es el de ProductoSeeder.
        $this->call([
            UserSeeder::class,
            PropietariosSeeder::class,
            ExplotacionesSeeder::class,
            ParcelasSeeder::class,
            ProductoSeeder::class,
            ProveedoresSeeder::class,
        ]);
    }
}