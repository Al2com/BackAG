<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Explotacion; //importa el modelo


class ExplotacionController extends Controller
{
    public function Explotaciones(){

            $explotaciones=Explotacion::all();
            $nomExplo=$explotaciones-> pluck('nombre');
            $numExplo = $explotaciones->count();

        return view('explotaciones',compact('explotaciones','numExplo'));
        // return $nomExplo;
    }

    // public function numExplo(){
    //     $explotaciones=Explotacion::all();
    //     $numExplo = $explotaciones->count();

    //     //  return view('explotaciones',compact('explotaciones', 'numExplo'));
    //     return view('explotaciones',compact('numExplo'));


    // }
    public function numeroExplo(){
        $numExplo = Explotacion::count(); // Cuenta directamente sin traer todos los registros
         $nomExplo = Explotacion::pluck('nombre');

        return response()->json(['total' => $numExplo , 'nom' => $nomExplo]);
    }

//INSERTAR DATOS

    public function insertar(Request $request){

            $explotacion=$request->validate([
            'nombre' => 'required|max:25',
            'ubicacion' => 'required',
            'descripcion' => 'required',
            'user_id' => 'required',
            'propietario_id' => 'required',
            ]);

             Explotacion::create($explotacion);


             return redirect()->route('insertarExplo');
    }



}

