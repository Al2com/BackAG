<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Explotacion;
use Illuminate\Validation\Rule;

class ExplotacionController extends Controller
{
    public function numeroExplo(){
        $numExplo = Explotacion::count();
        $nomExplo = Explotacion::select('id', 'nombre')->get();
        return response()->json(['total' => $numExplo, 'nom' => $nomExplo]);
    }

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

   public function crear(Request $request){
        $explotacion = $request->validate([
            'nombre'         => 'required|max:25',
            'ubicacion'      => 'required',
            'descripcion'    => 'required',
            'propietario_id' => ['required', Rule::exists('propietarios', 'id')->where('admin_id', $request->user()->adminId())],
        ]);

        Explotacion::create($explotacion);

        return response()->json(['mensaje' => 'Explotacion creada'], 201);
    }

    public function show($id){
        $explotacion = Explotacion::findOrFail($id);
        return response()->json($explotacion);
    }

    public function actualizar(Request $request, $id){
        $explotacion = Explotacion::findOrFail($id);

        $validacion = $request->validate([
            'nombre'         => 'required|max:25',
            'ubicacion'      => 'required',
            'descripcion'    => 'required',
            'propietario_id' => ['required', Rule::exists('propietarios', 'id')->where('admin_id', $request->user()->adminId())],
        ]);

        $explotacion->update($validacion);

        return response()->json(['mensaje' => 'Explotación actualizada correctamente'], 200);
    }

    public function borrarExplo($id){
        $explotacion = Explotacion::findOrFail($id);
        $explotacion->delete();
        return response()->json(['message' => 'Explotación eliminada correctamente']);
    }
}