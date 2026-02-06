<?php

namespace App\Http\Controllers;

use App\Models\Parcela; //importar el modelo simpre tiene que estar en mayuscula


use Illuminate\Http\Request;


class ParcelaController extends Controller
{
    // public function mostrarParcelas(){
    //     return $parcela = Parcela::all();
    // }

    public function numParcelas(){
        $numParcelas= Parcela::count();
        return response()->json(['total'=>$numParcelas]);
    }

    //enviando a la vista



    public function vistaParcelas(){
         $TotalHng=0;
        $parcelas=Parcela::all();
        $parcelasTotal=Parcela::count();
        foreach($parcelas as $parcela){
            $TotalHng+=$parcela->total_hanegadas;
        }
         dd($TotalHng);

        return view('parcelas',compact('parcelas','parcelasTotal','TotalHng'));
    }




}
