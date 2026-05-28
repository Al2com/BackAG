<?php


namespace App\Http\Controllers;
use App\Models\Operacion;
use App\Models\Fumigacion;

use Illuminate\Http\Request;


class TareasController extends Controller{
    // traigo todas las operaciones y fumigaciones con sus relaciones
    // para mostrarlas en la pantalla de tareas
    public function listar(){
     $operaciones = Operacion::with('parcela')->get();
     $fumigaciones = Fumigacion::with(['parcela', 'productos'])->get();

        return response()->json([
            'operaciones' => $operaciones,
            'fumigaciones' => $fumigaciones
             ]);
    }

  // marco una tarea como realizada, puede ser operacion o fumigacion
    // dependiendo del tipo que me llegue busco en un modelo u otro
     public function marcarRealizada($tipo, $id){
        if($tipo === 'operacion'){
            $tarea = Operacion::find($id);
        } else {
            $tarea = Fumigacion::find($id);
        }

        $tarea->estado = 'realizada';
        $tarea->save();

        return response()->json(['mensaje' => 'Tarea marcada como realizada']);
    }


    public function marcarRevisada($tipo, $id){
        if($tipo === 'operacion'){
            $tarea = Operacion::find($id);
        } else {
            $tarea = Fumigacion::find($id);
        }

        $tarea->estado = 'revisada';
        $tarea->save();

        return response()->json(['mensaje' => 'Tarea marcada como revisada']);
    }
   // traigo solo las 3 ultimas operaciones y fumigaciones para el widget
// de actividad reciente del dashboard, ordenadas por fecha de creacion
//latest ultimas y take(3) las 3  
    public function actividadReciente(){
        $operaciones= Operacion::select(['operario', 'tipo_operacion', 'estado'])->latest()->take(3)->get() ;     
        $fumigaciones=Fumigacion::select(['operario', 'metodo_aplicacion', 'estado'])->latest()->take(3)->get();
        
        return response()->json(['operaciones' => $operaciones,'fumigaciones' => $fumigaciones]);
    }
}
