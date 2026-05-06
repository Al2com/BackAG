<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Explotacion;

class ExplotacionController extends Controller
{
    // Devuelve el número total de explotaciones y sus nombres
    public function numeroExplo(){
        $numExplo = Explotacion::count();
        $nomExplo = Explotacion::select('id', 'nombre')->get();

        return response()->json(['total' => $numExplo , 'nom' => $nomExplo]);
    }

    // Devuelve un resumen de cada explotacion con sus parcelas, hanegadas totales, riego goteo y manta
    public function resumenExplotaciones(){
        $resumen = Explotacion::withCount('parcelas')
            ->withSum('parcelas', 'dimension_hanegadas')
            ->withCount(['parcelas as parcelas_goteo' => function($query){
                $query->where('rol', 'goteo');
            }])
            ->withCount(['parcelas as parcelas_manta' => function($query){
                $query->where('rol', 'manta');
            }])
            ->get(['id', 'nombre', 'ubicacion']);

        return response()->json($resumen);
    }

    // Crea una nueva explotacion con los datos que llegan desde el formulario
    public function crear(Request $request){
        $explotacion = $request->validate([
            'nombre'         => 'required|max:25',
            'ubicacion'      => 'required',
            'descripcion'    => 'required',
            'user_id'        => 'required',
            'propietario_id' => 'required',
        ]);

        Explotacion::create($explotacion);

        return response()->json(['mensaje' => 'Explotacion creada'], 201);
    }

    // Busca una explotacion por su ID y devuelve sus datos para cargarlos en el formulario de edición
    public function show($id){
        $explotacion = Explotacion::findOrFail($id);
        return response()->json($explotacion);
    }

    // Recibe los datos modificados del formulario y actualiza la explotacion en la base de datos
    public function actualizar(Request $request, $id){
        $explotacion = Explotacion::findOrFail($id);

        $validacion = $request->validate([
            'nombre'         => 'required|max:25',
            'ubicacion'      => 'required',
            'descripcion'    => 'required',
            'user_id'        => 'required',
            'propietario_id' => 'required',
        ]);

        $explotacion->update($validacion);

        return response()->json(['mensaje' => 'Explotación actualizada correctamente'], 200);
    }
}
