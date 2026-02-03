<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Explotacion; //importa el modelo


class ExplotacionController extends Controller
{
    public function mostrarExplotaciones(){

        $explotaciones=Explotacion::all();

        return view('explotaciones',compact('explotaciones'));
    }

    // public function numeroExplo(){
    //     $explotaciones=Explotacion::all();
    //     $numExplo = $explotaciones->count();

    //      return view('explotaciones',compact('explotaciones', 'numExplo'));


    // }
    public function numeroExplo(){
        $numExplo = Explotacion::count(); // Cuenta directamente sin traer todos los registros
        return response()->json(['total' => $numExplo]);
}

}

