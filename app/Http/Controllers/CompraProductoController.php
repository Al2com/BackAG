<?php

namespace App\Http\Controllers;

use App\Models\CompraProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompraProductoController extends Controller
{
    // Filtra por user_id para que cada usuario solo vea sus compras
    public function listar()
    {
        $compras = CompraProducto::where('user_id', Auth::id())
            ->with(['producto', 'proveedor', 'user'])
            ->get();
        return response()->json($compras);
    }

    public function crear(Request $request)
    {
        $datos = $request->validate([
            'producto_id'     => 'required|exists:productos,id',
            'proveedor_id'    => 'required|exists:proveedores,id',
            'cantidad_compra' => 'required|numeric|min:0',
            'precio'          => 'required|numeric|min:0',
            'fecha_compra'    => 'required|date',
        ]);

        $datos['user_id'] = Auth::id(); // ya estaba correcto

        $compra = CompraProducto::create($datos);

        $compra->producto->increment('stock_actual', $datos['cantidad_compra']);

        return response()->json(['mensaje' => 'Compra registrada', 'compra' => $compra], 201);
    }
}