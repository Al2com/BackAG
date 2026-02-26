<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Explotacion; //importa el modelo


class ExplotacionController extends Controller
{

    //CONSULTAS DESDE REACT
    public function numeroExplo(){
        $numExplo = Explotacion::count(); // Cuenta directamente sin traer todos los registros
        $nomExplo = Explotacion::select('id', 'nombre')->get();

        return response()->json(['total' => $numExplo , 'nom' => $nomExplo]);
    }

       // PARA EXPLOTACION

public function resumenExplotaciones(){
    $resumen = Explotacion::withCount('parcelas') // total de parcelas
        ->withSum('parcelas', 'dimension_hanegadas') // suma de hanegadas
        ->withCount(['parcelas as parcelas_goteo' => function($query){
            $query->where('rol', 'goteo'); // cuenta parcelas goteo
        }])
        ->withCount(['parcelas as parcelas_manta' => function($query){
            $query->where('rol', 'manta'); // cuenta parcelas manta
        }])
        ->get(['id', 'nombre', 'ubicacion']); // columnas de la tabla explotaciones

    return response()->json($resumen);
}




//INSERTAR DATOS

    public function crear(Request $request){

            $explotacion=$request->validate([
            'nombre' => 'required|max:25',
            'ubicacion' => 'required',
            'descripcion' => 'required',
            'user_id' => 'required',
            'propietario_id' => 'required',
            ]);

             Explotacion::create($explotacion);


             return response()->json(['mensaje' => 'Explotacion creada'], 201);
    }


    public function editar($id){
            $explo = Explotacion::findOrFail($id);

            return view('editar', compact('explo'));
}


public function actualizar(Request $request)
    {
        $validacion = $request->validate([
            'nombre' => 'required|min:1|max:10',
            'descripcion' => 'required',
        ]);

        Dato::findOrFail($request->id)->update($validacion);

        /*  //otra forma de almacenar
         $datos = Dato::find($id);   //podremos utilizar findOrFail($id) para que en caso de no encontrar no falle
         $datos->nombre = $validacion['nombre'];
         $datos->descripcion = $validacion['descripcion'];
         $datos->update();*/

        return redirect()->route('index');
}




}

