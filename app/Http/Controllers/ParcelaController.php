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

     return response()->json(['total'=>$numParcelas,'totalHng'=>$TotalHng,'parcelasgoteo'=> $parGot, 'parcelasmanta'=> $parMan]);

    }



    public function resumenDetallado() {
    // Usamos 'with' para traer el nombre de la explotación sin hacer 100 consultas (Eager Loading)
    $resumDatParcelas = Parcela::with('explotacion:id,nombre')
        ->get([
            'pol_parcela',
            'dimension_hanegadas',
            'variedad',
            'explotacion_id',
            'rol'
        ]);

    return response()->json($resumDatParcelas);
}



    //enviando a la vista



    public function vistaParcelas(){
        $TotalHng=0;
        $parcelas=Parcela::all();
        $parcelasTotal=Parcela::count();
        foreach($parcelas as $parcela){
            $TotalHng+=$parcela->dimension_hanegadas;
        }


        return view('parcelas',compact('parcelas','parcelasTotal','TotalHng'));
        // return respone->json(['totalHanegadas',$TotalHng]);
    }



    ¡


    public function crearParcela(Request $request){

            $parcela=$request->validate([
            'poligono' => 'required|max:25',
            'parcela' => 'required',
            'variedad' => 'required',
            'dimension_hanegadas' => 'required',
            'fecha_plantacion' => 'required',
            'descripcion' => 'required',
            ]);

             Parcela::create($parcela);


             return response()->json(['mensaje' => 'Parcela creada'], 201);
    }

}
