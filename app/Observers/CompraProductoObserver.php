<?php

namespace App\Observers;

use App\Models\CompraProducto;
use App\Exceptions\StockNegativoException;

/**
 * Punto único para mover el stock al registrar/borrar una compra. Vive aquí
 * (y no en el controlador) para que el efecto se dispare siempre que se cree o
 * borre una compra —también desde seeders o consola— y para centralizar la
 * regla de "nunca negativo", igual que Producto::descontarStock() en las bajas.
 */
class CompraProductoObserver
{
    // Al crear una compra entra stock en el producto.
    public function created(CompraProducto $compra): void
    {
        // consulta fresca (no la relación cacheada): el stock debe ser el actual
        $producto = $compra->producto()->first();
        if (! $producto) {
            return;
        }

        $producto->stock_actual = (float) $producto->stock_actual + (float) $compra->cantidad_compra;
        $producto->save();
    }

    // Al borrar (soft delete) sale stock; si quedara negativo, se bloquea.
    public function deleting(CompraProducto $compra): void
    {
        // consulta fresca: nunca la relación cacheada, para leer el stock de ahora
        $producto = $compra->producto()->first();
        if (! $producto) {
            return;
        }

        $resultante = (float) $producto->stock_actual - (float) $compra->cantidad_compra;

        if ($resultante < 0) {
            throw new StockNegativoException(
                'No se puede borrar la compra: dejaría el stock de "' . $producto->nombre . '" en negativo.'
            );
        }

        $producto->stock_actual = $resultante;
        $producto->save();
    }
}
