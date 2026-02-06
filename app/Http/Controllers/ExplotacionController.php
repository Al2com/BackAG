<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Explotacion; //importa el modelo


class ExplotacionController extends Controller
{
    public function mostrarExplotaciones(){

            $explotaciones=Explotacion::all();
            $numExplo = $explotaciones->count();

        return view('explotaciones',compact('explotaciones','numExplo'));
    }

    // public function numExplo(){
    //     $explotaciones=Explotacion::all();
    //     $numExplo = $explotaciones->count();

    //     //  return view('explotaciones',compact('explotaciones', 'numExplo'));
    //     return view('explotaciones',compact('numExplo'));


    // }
    public function numeroExplo(){
        $numExplo = Explotacion::count(); // Cuenta directamente sin traer todos los registros
        return response()->json(['total' => $numExplo]);
    }



}

