<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;


class AlmacenController extends Controller
{
      public function crear(Request $request){

            $producto=$request->validate([
            'nombre' => 'required|max:25',
            'materia_activa' => 'required',
            'ubicacion' => 'required',
            'stock_minimo' => 'required',
            ]);

             Producto::create($producto);


             return response()->json(['mensaje' => 'Producto creado en el alamacén'], 201);
    }
//Info stock bajo dashboard
      public function stockBajo(){
            $productos = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
                  ->select('id', 'nombre', 'stock_actual', 'stock_minimo', 'unidad')
                  ->get();
            return response()->json($productos);
}

}
