<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Administrador',
            'email'    => 'superadmin@test.com',
            'password' => Hash::make('admin'),
            'rol'      => 'superadmin',
        ]);

        User::create([
            'name'     => 'Encargado',
            'email'    => 'admin@test.com',
            'password' => Hash::make('encargado'),
            'rol'      => 'admin',
        ]);

        User::create([
            'name'     => 'Trabajador',
            'email'    => 'trabajador@test.com',
            'password' => Hash::make('trabajador'),
            'rol'      => 'trabajador',
        ]);
    }
}
