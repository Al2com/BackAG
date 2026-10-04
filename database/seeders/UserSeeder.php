<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
   public function run(): void
{
    // Admin Álvaro — id=1, el que usa el ExplotacionesSeeder
    $adminAlvaro = User::create([
        'name'     => 'Álvaro',
        'email'    => 'alvaro@test.com',
        'password' => Hash::make('admin1234'),
        'rol'      => 'admin',
    ]);

    User::create([
        'name'     => 'Eugen Setecu Malvert',
        'email'    => 'trabajador@test.com',
        'password' => Hash::make('trabajador1234'),
        'rol'      => 'trabajador',
        'admin_id' => $adminAlvaro->id,
    ]);

    // Admin Invitado (cuenta del tester) — id=3
    $adminInvitado = User::create([
        'name'     => 'Invitado',
        'email'    => 'invitado@test.com',
        'password' => Hash::make('invitado1234'),
        'rol'      => 'admin',
    ]);

    User::create([
        'name'     => 'Trabajador Andrés 1',
        'email'    => 'trabajador.andres1@test.com',
        'password' => Hash::make('trabajador1234'),
        'rol'      => 'trabajador',
        'admin_id' => $adminInvitado->id,
    ]);

    // Admin 2
    $adminDos = User::create([
        'name'     => 'Usuario 2',
        'email'    => 'usuario2@test.com',
        'password' => Hash::make('admin1234'),
        'rol'      => 'admin',
    ]);

    User::create([
        'name'     => 'Trabajador Usuario 2',
        'email'    => 'trabajador.usuario2@test.com',
        'password' => Hash::make('trabajador1234'),
        'rol'      => 'trabajador',
        'admin_id' => $adminDos->id,
    ]);

    // Superadmin
    User::create([
        'name'     => 'Superadmin',
        'email'    => 'superadmin@test.com',
        'password' => Hash::make('admin1234'),
        'rol'      => 'superadmin',
    ]);
}
}