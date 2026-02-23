<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Http\Propietario;

class PropietarioControlador extends Controller
{
    public function mostrarPropietarios(){
        $propietarios= Propietarios::all();
         return response()->json(['propietarios' => $propietarios]);
    }
}
