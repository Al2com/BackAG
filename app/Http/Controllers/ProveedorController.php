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

    public function crear(Request $request) {
        $datos = $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'direccion'      => 'required|string|max:255',
            'telefono'       => 'nullable|string|max:30',
        ]);

        $proveedor = Proveedor::create($datos); // admin_id lo pone el trait PerteneceAdmin

        return response()->json($proveedor, 201);
    }
}
