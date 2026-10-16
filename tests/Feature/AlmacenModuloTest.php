<?php

use App\Models\User;
use App\Models\Producto;
use App\Models\Operacion;
use App\Models\CompraProducto;
use Illuminate\Support\Facades\DB;

// --- helpers (nombres únicos para no chocar con otros ficheros de test) ---

function almParcela(User $admin): int
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

function almProducto(array $extra = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Cesar', 'materia_activa' => 'm', 'categoria' => 'Fungicida',
        'ubicacion' => 'u', 'stock_minimo' => 5, 'stock_maximo' => 25,
        'stock_actual' => 0, 'unidad' => 'kg',
    ], $extra));
}

// uso de ESTE año: una operación de abonado que consume el producto
function almUsoAbonado(User $admin, Producto $p, int $parcelaId, float $dosis): void
{
    Operacion::create([
        'parcela_id' => $parcelaId, 'usuario_id' => $admin->id, 'operario' => 'x',
        'tipo_operacion' => 'abonado', 'producto_id' => $p->id, 'dosis' => $dosis,
        'hora_inicio' => now(), 'duracion_minutos' => 10, 'precio' => 0, 'descripcion' => 'x',
    ]);
}

// --- tests ---

test('clasificado separa frecuentes (con consumo este año) de menos frecuentes', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $usado = almProducto(['nombre' => 'Cesar']);
    $sinUso = almProducto(['nombre' => 'Aliette']);
    almUsoAbonado($admin, $usado, almParcela($admin), 4);

    $r = $this->getJson('/api/almacen/clasificado')->assertOk();

    $frec = collect($r->json('frecuentes'));
    $menos = collect($r->json('menos_frecuentes'));

    expect($frec->firstWhere('id', $usado->id))->not->toBeNull()
        ->and((float) $frec->firstWhere('id', $usado->id)['consumo_anual'])->toBe(4.0)
        ->and($frec->firstWhere('id', $usado->id)['operaciones_count'])->toBe(1)
        ->and($frec->firstWhere('id', $sinUso->id))->toBeNull()
        ->and($menos->firstWhere('id', $sinUso->id))->not->toBeNull()
        ->and($menos->firstWhere('id', $usado->id))->toBeNull();
});

test('historial devuelve compras y usos del año', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $p = almProducto();
    almUsoAbonado($admin, $p, almParcela($admin), 3);

    CompraProducto::create([
        'producto_id' => $p->id, 'cantidad_compra' => 10, 'precio' => 12,
        'fecha_compra' => now(), 'proveedor_nombre' => 'Agroquímica Alzira',
    ]);

    $r = $this->getJson("/api/productos/{$p->id}/historial")->assertOk();

    expect($r->json('compras'))->toHaveCount(1)
        ->and($r->json('usos'))->toHaveCount(1);

    $compra = $r->json('compras.0');
    expect((float) $compra['cantidad'])->toBe(10.0)
        ->and((float) $compra['importe_total'])->toBe(120.0)
        ->and($compra['proveedor'])->toBe('Agroquímica Alzira');

    $uso = $r->json('usos.0');
    expect($uso['tipo_operacion'])->toBe('abonado')
        ->and((float) $uso['cantidad'])->toBe(3.0)
        ->and($uso['parcela']['poligono'])->toBe(24);
});

test('POST /api/compras crea la compra e incrementa el stock', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $p = almProducto(['stock_actual' => 10]);

    $this->postJson('/api/compras', [
        'producto_id' => $p->id, 'fecha' => now()->toDateString(),
        'cantidad' => 6, 'precio_unitario' => 3, 'proveedor' => 'Agro X',
        'albaran' => 'A-1', 'notas' => 'prueba',
    ])->assertCreated();

    expect((float) $p->fresh()->stock_actual)->toBe(16.0)
        ->and(CompraProducto::where('producto_id', $p->id)->first()->proveedor_nombre)->toBe('Agro X');
});

test('un admin no accede al historial ni compra el producto de otro', function () {
    $dueno = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($dueno, 'sanctum');
    $p = almProducto();

    $otro = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($otro, 'sanctum');

    $this->getJson("/api/productos/{$p->id}/historial")->assertNotFound();
    $this->postJson('/api/compras', [
        'producto_id' => $p->id, 'fecha' => now()->toDateString(), 'cantidad' => 2,
    ])->assertStatus(422); // el producto no es suyo (regla exists)
});

test('ajustar stock fija el stock del producto al valor contado', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $p = almProducto(['stock_actual' => 40]);

    $this->putJson("/api/productos/{$p->id}/stock", ['stock_actual' => 12.5])
        ->assertOk()
        ->assertJsonPath('stock_actual', '12.50');

    expect((float) $p->fresh()->stock_actual)->toBe(12.5);
});

test('un admin no ajusta el stock del producto de otro', function () {
    $dueno = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($dueno, 'sanctum');
    $p = almProducto();

    $otro = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($otro, 'sanctum');

    $this->putJson("/api/productos/{$p->id}/stock", ['stock_actual' => 5])->assertNotFound();
});
