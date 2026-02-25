<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
 public function run(): void
{
    User::factory()->create([
        'name' => 'Alvaro',
        'email' => 'alvaro@alvaro.com',
        'password' => bcrypt('alvarocomenge'),
    ]);

    $this->call([
        PropietariosSeeder::class,
        ExplotacionesSeeder::class,
        ParcelasSeeder::class,
    ]);
}
}
