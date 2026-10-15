<?php

namespace App\Exceptions;

use Exception;

/**
 * Se lanza cuando borrar una compra dejaría el stock del producto en negativo.
 * Se traduce a un 422 con mensaje claro para el cliente.
 */
class StockNegativoException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'mensaje' => $this->getMessage() ?: 'No se puede borrar la compra: dejaría el stock en negativo.',
        ], 422);
    }
}
