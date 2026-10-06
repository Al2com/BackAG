<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

// Monta los datos mínimos de un inquilino con un riego a manta.
function sembrarRiegoManta(User $admin): void
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
    $parcelaId = DB::table('parcelas')->insertGetId([
        'explotacion_id' => $exploId, 'propietarios_id' => $propId, 'rol' => 'manta',
        'poligono' => 1, 'parcela' => 1, 'nombre' => 'Pa', 'variedad' => 'navel',
        'dimension_hanegadas' => 10, 'num_arboles' => 100, 'fecha_plantacion' => now(),
        'descripcion' => 'x', 'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('riegos_manta')->insert([
        'parcela_id' => $parcelaId, 'fecha' => now()->toDateString(),
        'precio_por_hanegada' => 2, 'hanegadas' => 10, 'importe' => 20,
        'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('el respaldo JSON incluye el riego a manta y se conserva al importar', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    sembrarRiegoManta($admin);

    // exportar
    $export = $this->actingAs($admin, 'sanctum')->get('/api/backup/json');
    $export->assertOk();
    $json = $export->streamedContent();

    expect($json)->toContain('riegos_manta');

    // reimportar el mismo respaldo: la importación borra y vuelve a insertar
    $archivo = UploadedFile::fake()->createWithContent('backup.json', $json);
    $this->actingAs($admin, 'sanctum')
        ->post('/api/backup/importar', ['archivo' => $archivo])
        ->assertOk();

    // el riego a manta sobrevive (antes del fix se perdía en cascada)
    expect(DB::table('riegos_manta')->where('admin_id', $admin->id)->count())->toBe(1);
});

test('rechaza un respaldo de versión antigua que no trae el riego a manta', function () {
    $admin = User::factory()->create(['rol' => 'admin']);

    $viejo = json_encode(['meta' => ['version_esquema' => 1], 'tablas' => []]);
    $archivo = UploadedFile::fake()->createWithContent('viejo.json', $viejo);

    $this->actingAs($admin, 'sanctum')
        ->post('/api/backup/importar', ['archivo' => $archivo])
        ->assertStatus(422);
});

test('al importar, las filas de riego a manta se fuerzan al inquilino que importa', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    sembrarRiegoManta($admin);

    $json = $this->actingAs($admin, 'sanctum')->get('/api/backup/json')->streamedContent();
    $datos = json_decode($json, true);

    // simula una copia manipulada: la fila de riego llega con admin_id de otro
    $datos['tablas']['riegos_manta'][0]['admin_id'] = 99999;
    $archivo = UploadedFile::fake()->createWithContent('backup.json', json_encode($datos));

    $this->actingAs($admin, 'sanctum')
        ->post('/api/backup/importar', ['archivo' => $archivo])
        ->assertOk();

    // ninguna fila queda con el admin_id ajeno: todas son del inquilino
    expect(DB::table('riegos_manta')->where('admin_id', 99999)->count())->toBe(0)
        ->and(DB::table('riegos_manta')->where('admin_id', $admin->id)->count())->toBe(1);
});
