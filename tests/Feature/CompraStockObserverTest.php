<?php

use App\Models\User;
use App\Models\Producto;
use App\Models\CompraProducto;
use App\Exceptions\StockNegativoException;

// producto mínimo del admin logueado
function productoConStock(float $stock): Producto
{
    return Producto::create([
        'nombre' => 'X', 'materia_activa' => 'm', 'ubicacion' => 'u',
        'stock_minimo' => 5, 'stock_actual' => $stock, 'unidad' => 'kg',
    ]);
}

function nuevaCompra(Producto $p, float $cantidad): CompraProducto
{
    return CompraProducto::create([
        'producto_id' => $p->id,
        'cantidad_compra' => $cantidad,
        'precio' => 2,
        'fecha_compra' => now(),
    ]);
}

test('crear una compra incrementa el stock una sola vez', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $p = productoConStock(10);
    nuevaCompra($p, 4);

    expect((float) $p->fresh()->stock_actual)->toBe(14.0);
});

test('borrar (soft delete) una compra decrementa el stock', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $p = productoConStock(10);
    $compra = nuevaCompra($p, 4);          // stock -> 14
    $compra->delete();                      // stock -> 10

    expect((float) $p->fresh()->stock_actual)->toBe(10.0)
        ->and(CompraProducto::withTrashed()->find($compra->id)->trashed())->toBeTrue();
});

test('borrar una compra que dejaría el stock negativo lanza excepción y no borra', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $p = productoConStock(0);
    $compra = nuevaCompra($p, 5);          // stock -> 5
    // se consume parte: dejo el stock por debajo de la cantidad comprada
    Producto::where('id', $p->id)->update(['stock_actual' => 1]);

    expect((float) $p->fresh()->stock_actual)->toBe(1.0); // control del escenario

    expect(fn () => $compra->delete())->toThrow(StockNegativoException::class);

    // ni se borró ni se tocó el stock
    expect(CompraProducto::find($compra->id))->not->toBeNull()
        ->and((float) $p->fresh()->stock_actual)->toBe(1.0);
});
