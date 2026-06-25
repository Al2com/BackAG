<?php

namespace App\Http\Controllers;

use App\Models\CompraProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'producto_id'     => ['required', Rule::exists('productos', 'id')->where('admin_id', $request->user()->adminId())],
            'proveedor_id'    => ['required', Rule::exists('proveedores', 'id')->where('admin_id', $request->user()->adminId())],
            'cantidad_compra' => 'required|numeric|min:0',
            'precio'          => 'required|numeric|min:0',
            'fecha_compra'    => 'required|date',
        ]);

        $datos['user_id'] = Auth::id();

        $compra = CompraProducto::create($datos);

        // Precio medio ponderado: mezcla lo que ya había con lo recién comprado
        $producto      = $compra->producto;
        $stockAnterior = (float) $producto->stock_actual;
        $costeAnterior = (float) ($producto->precio ?? 0);
        $cantidad      = (float) $datos['cantidad_compra'];
        $precioCompra  = (float) $datos['precio'];

        $stockTotal = $stockAnterior + $cantidad;

        $producto->precio = $stockTotal > 0
            ? round((($stockAnterior * $costeAnterior) + ($cantidad * $precioCompra)) / $stockTotal, 2)
            : $precioCompra;

        $producto->stock_actual = $stockTotal;
        $producto->save();

        return response()->json(['mensaje' => 'Compra registrada', 'compra' => $compra], 201);
    }
}