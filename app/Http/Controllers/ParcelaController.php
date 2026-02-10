<?php

namespace App\Http\Controllers;

use App\Models\Parcela; //importar el modelo simpre tiene que estar en mayuscula


use Illuminate\Http\Request;


class ParcelaController extends Controller
{
    // public function mostrarParcelas(){
    //     return $parcela = Parcela::all();
    // }

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

    public function infoParcelasExplotacion(){
        $totalesHngXExplo = Parcela::select('explotacion_id', DB::raw('SUM(dimension_hanegadas) as total_hanegadas'))
                    ->groupBy('explotacion_id')
                    ->get();
        return response()->json(['totales' => $totalesHngXExplo]);


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




}
