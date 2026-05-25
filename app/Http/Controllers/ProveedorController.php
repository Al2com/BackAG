<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function listar(){
        $proveedores = Proveedor::all();
        return response()->json($proveedores);
    }
}
