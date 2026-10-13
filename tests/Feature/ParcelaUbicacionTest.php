<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Crea una parcela mínima perteneciente a $admin y devuelve su id.
function crearParcelaDe(User $admin): int
{
    $propId = DB::table('propietarios')->insertGetId([
        'nombre' => 'P', 'dni' => '1', 'telefono' => '1',
        'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $exploId = DB::table('explotaciones')->insertGetId([
        'nombre' => 'E', 'ubicacion' => 'U', 'descripcion' => 'D',
        'admin_id' => $admin->id, 'propietario_id' => $propId,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return DB::table('parcelas')->insertGetId([
        'explotacion_id' => $exploId, 'propietarios_id' => $propId, 'rol' => 'goteo',
        'poligono' => 24, 'parcela' => 51, 'nombre' => 'Pa', 'variedad' => 'navel',
        'dimension_hanegadas' => 10, 'num_arboles' => 100, 'fecha_plantacion' => now(),
        'descripcion' => 'x', 'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('guarda y lee la ubicación de una parcela', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $id = crearParcelaDe($admin);

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/parcelas/$id/ubicacion", ['latitud' => 39.1938, 'longitud' => -0.5078])
        ->assertOk();

    $this->actingAs($admin, 'sanctum')
        ->getJson("/api/parcelas/$id/ubicacion")
        ->assertOk()
        ->assertJsonPath('latitud', '39.1938000')
        ->assertJsonPath('longitud', '-0.5078000');
});

test('un admin no accede a la ubicación de la parcela de otro', function () {
    $dueno = User::factory()->create(['rol' => 'admin']);
    $otro = User::factory()->create(['rol' => 'admin']);
    $id = crearParcelaDe($dueno);

    $this->actingAs($otro, 'sanctum')->getJson("/api/parcelas/$id/ubicacion")->assertNotFound();
    $this->actingAs($otro, 'sanctum')
        ->putJson("/api/parcelas/$id/ubicacion", ['latitud' => 39, 'longitud' => -0.5])
        ->assertNotFound();
});

test('rechaza coordenadas fuera de rango', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $id = crearParcelaDe($admin);

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/parcelas/$id/ubicacion", ['latitud' => 200, 'longitud' => 0])
        ->assertStatus(422);
});
