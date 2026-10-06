<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('crear operación con precio no numérico devuelve 422, no 500', function () {
    $admin = User::factory()->create(['rol' => 'admin']);

    $propId = DB::table('propietarios')->insertGetId([
        'nombre' => 'P', 'dni' => '1', 'telefono' => '1',
        'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $exploId = DB::table('explotaciones')->insertGetId([
        'nombre' => 'E', 'ubicacion' => 'U', 'descripcion' => 'D',
        'admin_id' => $admin->id, 'propietario_id' => $propId,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $parcelaId = DB::table('parcelas')->insertGetId([
        'explotacion_id' => $exploId, 'propietarios_id' => $propId, 'rol' => 'goteo',
        'poligono' => 1, 'parcela' => 1, 'nombre' => 'Pa', 'variedad' => 'navel',
        'dimension_hanegadas' => 10, 'num_arboles' => 100, 'fecha_plantacion' => now(),
        'descripcion' => 'x', 'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($admin, 'sanctum')->postJson('/api/operaciones/crear', [
        'parcela_id' => $parcelaId,
        'operario' => 'Juan',
        'tipo_operacion' => 'poda',
        'hora_inicio' => '2026-01-01 08:00:00',
        'duracion_minutos' => 60,
        'precio' => 'no-es-un-numero',
        'descripcion' => 'prueba',
    ])->assertStatus(422)->assertJsonValidationErrors('precio');
});
