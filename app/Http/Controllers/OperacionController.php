<?php
namespace App\Http\Controllers;

use App\Models\Operacion;
use Illuminate\Http\Request;

class OperacionController extends Controller
{
    public function crearOperacion(Request $request){
        $operacion = $request->validate([
            'parcela_id' => 'required',
            'usuario_id' => 'required',
            'tipo_operacion' => 'required',
            'hora_inicio' => 'required',
            'duracion_minutos' => 'required',
            'descripcion' => 'required',
        ]);

        Operacion::create($operacion);
        return response()->json(['mensaje' => 'Operación creada'], 201);
    }

    public function listar(){
    $operaciones = Operacion::all();
    return response()->json($operaciones);
}
}
