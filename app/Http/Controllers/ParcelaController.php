<?php

namespace App\Http\Controllers;

use App\Models\Parcela; //importar el modelo simpre tiene que estar en mayuscula


use Illuminate\Http\Request;


class ParcelaController extends Controller
{


    public function infoParcelas(){
        $numParcelas= Parcela::count();
        $TotalHng=0;
        $parGot=0;
        $parMan=0;

        $parcelas = Parcela::all();
        foreach($parcelas as $parcela){
            $TotalHng+=$parcela->dimension_hanegadas;

            if($parcela->rol==='goteo'){
                $parGot++;
            }

            if($parcela->rol==='manta'){
                $parMan++;
            }
        }

     return response()->json(['total'=>$numParcelas,'totalHng'=> round($TotalHng,2),'parcelasgoteo'=> $parGot, 'parcelasmanta'=> $parMan]);

    }



    public function resumenDetallado() {
    // Usamos 'with' para traer el nombre de la explotación sin hacer 100 consultas (Eager Loading)
    $resumDatParcelas = Parcela::with('explotacion:id,nombre')
      ->get([
        'id',
        'poligono',
        'parcela',
        'dimension_hanegadas',
        'variedad',
        'explotacion_id',
        'rol'
        ]);

    return response()->json($resumDatParcelas);
}


    public function crearParcela(Request $request){

            $parcela=$request->validate([
            'explotacion_id'=>'required',
            'propietarios_id'=>'required',
            'poligono' => 'required|max:25',
            'parcela' => 'required',
            'rol'=>'required',
            'variedad' => 'required',
            'num_arboles'=>'required',
            'dimension_hanegadas' => 'required',
            'fecha_plantacion' => 'required',
            'descripcion' => 'required',
            ]);

             Parcela::create($parcela);


             return response()->json(['mensaje' => 'Parcela creada'], 201);
    }

}
