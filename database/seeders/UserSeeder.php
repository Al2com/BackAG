<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin — ve todo, sin restricciones
        User::create([
            'name'     => 'Álvaro',
            'email'    => 'superadmin@test.com',
            'password' => Hash::make('admini'),
            'rol'      => 'superadmin',
        ]);

        // Admin Álvaro — gestiona su propia explotación
        $adminAlvaro = User::create([
            'name'     => 'Álvaro',
            'email'    => 'admin@test.com',
            'password' => Hash::make('encargado'),
            'rol'      => 'admin',
        ]);

        User::create([
            'name'     => 'Eugen Setecu Malvert',
            'email'    => 'trabajador@test.com',
            'password' => Hash::make('trabajador1234'),
            'rol'      => 'trabajador',
            'admin_id' => $adminAlvaro->id,
        ]);

        // Admin Andrés
        $adminAndres = User::create([
            'name'     => 'Andrés',
            'email'    => 'andresadmin@agroalgestion.com',
            'password' => Hash::make('admin1234'),
            'rol'      => 'admin',
        ]);

        User::create([
            'name'     => 'Trabajador Andrés 1',
            'email'    => 'trabajadorandres1@agroalgestion.com',
            'password' => Hash::make('trabajador1234'),
            'rol'      => 'trabajador',
            'admin_id' => $adminAndres->id,
        ]);

        User::create([
            'name'     => 'Trabajador Andrés 2',
            'email'    => 'trabajadorandres2@agroalgestion.com',
            'password' => Hash::make('trabajador1234'),
            'rol'      => 'trabajador',
            'admin_id' => $adminAndres->id,
        ]);

        // Admin 2 — pendiente de identificar
        $adminDos = User::create([
            'name'     => 'Admin 2',
            'email'    => 'admin2@agroalgestion.com',
            'password' => Hash::make('admin1234'),
            'rol'      => 'admin',
        ]);

        User::create([
            'name'     => 'Trabajador Usuario 1',
            'email'    => 'trabajadorusuario1@agroalgestion.com',
            'password' => Hash::make('trabajador1234'),
            'rol'      => 'trabajador',
            'admin_id' => $adminDos->id,
        ]);

        User::create([
            'name'     => 'Trabajador Usuario 2',
            'email'    => 'trabajadorusuario2@agroalgestion.com',
            'password' => Hash::make('trabajador1234'),
            'rol'      => 'trabajador',
            'admin_id' => $adminDos->id,
        ]);
    }
}