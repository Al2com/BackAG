<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function mostrarProductos(){
        $productos=Producto::select(
        'id',    
        'nombre',
        'materia_activa',
        'precio',
        'ubicacion',
        'dosis_recomendada',
        'stock_actual',
        'unidad')->get();
        return response()->json($productos);

    }

      //para sacar el producto a modificar GET /api/productos/{id}
    public function modificarProducto($id){
        $producto = Producto::findOrFail($id);
        return response()->json($producto);
    }

    // para modificar el procuto elegido por id PUT /api/productos/{id}
    public function actualizarProducto(Request $request, $id){
        $producto = Producto::findOrFail($id);
        $producto->update($request->all());
        return response()->json($producto);
    }
}

