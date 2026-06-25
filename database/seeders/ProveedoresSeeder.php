<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

// Siembra los proveedores para CADA admin con su propio admin_id. Antes se
// insertaban sin admin_id (quedaban huérfanos con admin_id null), lo que rompía
// la validación de compras (exists ... where admin_id). Aquí van por admin.
//
// Se usa DB::table()->insert(): el query builder inserta el admin_id tal cual,
// sin pasar por $fillable (donde admin_id ya no está tras la Fase 1).
class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        $plantilla = [
            [
                'nombre_empresa'   => 'Tienda Cooperativa',
                'direccion'        => 'Calle Mayor, 1, Valencia',
                'telefono'         => '962540509',
                'nombre_comercial' => 'Cooperativa Agrícola',
                'descripcion'      => 'Proveedor cooperativo de productos agrícolas y suministros para el campo.',
            ],
            [
                'nombre_empresa'   => 'Rafael Martínez SL',
                'direccion'        => 'Avenida del Puerto, 45, Valencia',
                'telefono'         => '962345678',
                'nombre_comercial' => 'Rafael Martínez',
                'descripcion'      => 'Empresa proveedora de materiales y servicios para explotaciones agrícolas.',
            ],
        ];

        $admins = User::where('rol', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->command->warn('No hay admins (¿ejecutaste UserSeeder antes?). No se siembran proveedores.');
            return;
        }

        $filas = [];
        foreach ($admins as $admin) {
            foreach ($plantilla as $proveedor) {
                $filas[] = $proveedor + [
                    'admin_id'   => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('proveedores')->insert($filas);
    }
}