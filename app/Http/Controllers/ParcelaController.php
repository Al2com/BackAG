<?php

namespace App\Http\Controllers;

use App\Models\Parcela; //importar el modelo simpre tiene que estar en mayuscula

use Illuminate\Http\Request;


class ParcelaController extends Controller
{
    public function mostrarParcelas(){
        return $parcela = Parcela::all();
    }

    public function numParcelas(){
        $numParcelas= Parcela::count();
        return response()->json(['total'=>$numParcelas]);
    }

}
