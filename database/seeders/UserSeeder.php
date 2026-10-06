<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin: ve los datos de todos los admins (sin filtro de AdminScope)
        User::create([
            'name'     => 'Superadmin',
            'email'    => 'superadmin@test.com',
            'password' => Hash::make('admin1234'),
            'rol'      => 'superadmin',
        ]);

        // Admin Álvaro — id del que usan ExplotacionesSeeder y ParcelasSeeder
        $adminAlvaro = User::create([
            'name'     => 'Álvaro',
            'email'    => 'alvaro@test.com',
            'password' => Hash::make('admin1234'),
            'rol'      => 'admin',
        ]);

        // Trabajador 1 — cuelga de Álvaro
        User::create([
            'name'     => 'Trabajador 1',
            'email'    => 'trabajador1@test.com',
            'password' => Hash::make('trabajador1234'),
            'rol'      => 'trabajador',
            'admin_id' => $adminAlvaro->id,
        ]);

        // Admin Invitado (cuenta del tester)
        $adminInvitado = User::create([
            'name'     => 'Invitado',
            'email'    => 'invitado@test.com',
            'password' => Hash::make('admin1234'),
            'rol'      => 'admin',
        ]);

        // Trabajador 2 — cuelga de Invitado
        User::create([
            'name'     => 'Trabajador 2',
            'email'    => 'trabajador2@test.com',
            'password' => Hash::make('trabajador1234'),
            'rol'      => 'trabajador',
            'admin_id' => $adminInvitado->id,
        ]);
    }
}
