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
        $parcelas = Parcela::all();
        foreach($parcelas as $parcela){
            $TotalHng+=$parcela->dimension_hanegadas;

        }
     return response()->json(['total'=>$numParcelas,'totalHng'=>$TotalHng]);
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
