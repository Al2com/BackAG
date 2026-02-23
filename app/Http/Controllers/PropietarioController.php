<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Propietario;

class PropietarioController extends Controller
{
    public function mostrarPropietarios(){
        $propietarios= Propietario::all();
         return response()->json(['propietarios' => $propietarios]);
    }
}
