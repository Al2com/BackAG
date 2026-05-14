<?php

namespace App\Http\Controllers;

use App\Models\CompraProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompraProductoController extends Controller
{
    // GET /api/compras
    public function listar()
    {
        $compras = CompraProducto::with(['producto', 'proveedor', 'user'])->get();
        return response()->json($compras);
    }

    // POST /api/compras
    public function crear(Request $request)
    {
        $datos = $request->validate([
            'producto_id'   => 'required|exists:productos,id',
            'proveedor_id'  => 'required|exists:proveedores,id',
            'cantidad_compra' => 'required|numeric|min:0',
            'precio'        => 'required|numeric|min:0',
            'fecha_compra'  => 'required|date',
        ]);

        $datos['user_id'] = Auth::id();

        $compra = CompraProducto::create($datos);

        // Incrementar stock del producto
        $compra->producto->increment('stock_actual', $datos['cantidad_compra']);

        return response()->json(['mensaje' => 'Compra registrada', 'compra' => $compra], 201);
    }
}