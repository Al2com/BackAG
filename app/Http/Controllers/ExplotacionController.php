<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Explotacion; //importa el modelo

class ExplotacionController extends Controller
{
    public function mostrarExplotaciones(){
        return $explotaciones = explotacion::all();
    }
    //probando el ssh   
}

