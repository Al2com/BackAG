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
            'name'     => 'Álvaro',
            'email'    => 'superadmin@test.com',
            'password' => Hash::make('admini'),
            'rol'      => 'superadmin',
        ]);

        User::create([
            'name'     => 'Álvaro',
            'email'    => 'admin@test.com',
            'password' => Hash::make('encargado'),
            'rol'      => 'admin',
        ]);

        User::create([
            'name'     => 'Eugen Setecu Malvert',
            'email'    => 'trabajador@test.com',
            'password' => Hash::make('trabajador'),
            'rol'      => 'trabajador',
        ]);
    }
}
