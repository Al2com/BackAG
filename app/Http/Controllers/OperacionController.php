<?php
namespace App\Http\Controllers;

use App\Models\Operacion;
use Illuminate\Http\Request;

class OperacionController extends Controller
{
    public function crearOperacion(Request $request){
        $operacion = $request->validate([
            'parcela_id'       => 'required',
            'operario'         => 'required',
            'tipo_operacion'   => 'required',
            'hora_inicio'      => 'required',
            'duracion_minutos' => 'required',
            'precio'           => 'required',
            'descripcion'      => 'required',
        ]);

        $operacion['usuario_id'] = auth()->id(); // ya estaba correcto, se eliminó usuario_id de la validación
        Operacion::create($operacion);
        return response()->json(['mensaje' => 'Operación creada'], 201);
    }

    // Filtra por usuario_id para que cada usuario solo vea sus operaciones
    public function listar(){
        $operaciones = Operacion::where('usuario_id', auth()->id())->get();
        $totalOperaciones = $operaciones->count();

        return response()->json(['total' => $totalOperaciones, 'operaciones' => $operaciones]);
    }

    public function opercionId($id){
        $operacion = Operacion::findOrFail($id);
        return response()->json($operacion);
    }

    public function borrar($id) {
        $operacion = Operacion::findOrFail($id);
        $operacion->delete();
        return response()->json(['mensaje' => 'Operación eliminada correctamente']);
    }

    public function actualizar(Request $request, $id) {
        $operacion = Operacion::findOrFail($id);
        $operacion->update($request->all());
        return response()->json(['mensaje' => 'Operación actualizada']);
    }
}