<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function mostrarProductos(){
        $productos=Producto::select('nombre','dosis_recomendada','unidad')->get();
        return response()->json($productos);

    }
}

