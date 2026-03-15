<?php

namespace App\Http\Controllers;
use App\Models\Fumigacion;
use Illuminate\Http\Request;



class FumigacionController extends Controller{

    public function añadirFumigacion(Request $request){

        $datos = $request->validate([
            'parcela_id'        => 'required',
            'metodo_aplicacion' => 'required',
            'hora_inicio'       => 'required',
            'descripcion'       => 'required',
            'operario'          => 'required_if:metodo_aplicacion,mochila',
            'duracion_minutos'  => 'required_if:metodo_aplicacion,mochila',
            'mochilas'          => 'required_if:metodo_aplicacion,mochila',
            'turbos'            => 'required_if:metodo_aplicacion,tractor',
            'productos'         => 'required|array',
        ]);

        // 1. Crear la fumigacion
        $fumigacion = Fumigacion::create([
            'parcela_id'        => $datos['parcela_id'],
            'metodo_aplicacion' => $datos['metodo_aplicacion'],
            'hora_inicio'       => $datos['hora_inicio'],
            'descripcion'       => $datos['descripcion'],
            'operario'          => $datos['operario'] ?? null,
            'duracion_minutos'  => $datos['duracion_minutos'] ?? null,
            'mochilas'          => $datos['mochilas'] ?? null,
            'turbos'            => $datos['turbos'] ?? null,
        ]);

        // 2. Guardar cada producto en la tabla intermedia
        foreach($request->productos as $producto){
            $fumigacion->productos()->attach($producto['producto_id'], [
                'dosis_introducida' => $producto['dosis_introducida'],
                'cantidad'          => $producto['dosis_introducida'],
            ]);
        }

        return response()->json(['mensaje' => 'Fumigación creada'], 201);
    }

    public function listar(){
        $fumigaciones = Fumigacion::all();
        $totalFumigaciones= $fumigaciones->count();
        return response()->json(['fumigaciones' => $fumigaciones ,'totalFumigaciones'=> $totalFumigaciones]);
    }
}



