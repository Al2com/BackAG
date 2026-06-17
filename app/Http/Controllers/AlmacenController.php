<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;


class AlmacenController extends Controller
{
    public function crear(Request $request){

        $producto = $request->validate([
            'nombre' => 'required|max:50',
            'materia_activa' => 'required',
            'ubicacion' => 'required',
            'stock_minimo' => 'required',
            'precio' => 'nullable|numeric|min:0',
        ]);

        Producto::create($producto);

        return response()->json(['mensaje' => 'Producto creado en el almacén'], 201);
    }

    // Info stock bajo dashboard
    public function stockBajo(){
        $productos = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->select('id', 'nombre', 'stock_actual', 'stock_minimo', 'unidad')
            ->get();
        return response()->json($productos);
    }
}