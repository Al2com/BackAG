<?php
namespace App\Http\Controllers;
use App\Models\Fumigacion;
use App\Models\Producto;
use Illuminate\Http\Request;

class FumigacionController extends Controller {

    public function añadirFumigacion(Request $request) {
        $datos = $request->validate([
            'parcela_ids'       => 'required|array',
            'parcela_ids.*'     => 'required|exists:parcelas,id',
            'metodo_aplicacion' => 'required',
            'hora_inicio'       => 'required',
            'descripcion'       => 'required',
            'precio'            => 'required',
            // si es mochila
            'operario'          => 'required_if:metodo_aplicacion,mochila',
            'duracion_minutos'  => 'required_if:metodo_aplicacion,mochila',
            'mochilas'          => 'required_if:metodo_aplicacion,mochila',
            // si es tractor
            'turbos'            => 'required_if:metodo_aplicacion,tractor',
            'productos'         => 'required|array',
        ]);

        // una fumigacion por cada parcela seleccionada
        foreach ($datos['parcela_ids'] as $parcelaId) {
            $fumigacion = Fumigacion::create([
                'parcela_id'        => $parcelaId,
                'metodo_aplicacion' => $datos['metodo_aplicacion'],
                'hora_inicio'       => $datos['hora_inicio'],
                'descripcion'       => $datos['descripcion'],
                'precio'            => $datos['precio'],
                'operario'          => $datos['operario'] ?? null,
                'duracion_minutos'  => $datos['duracion_minutos'] ?? null,
                'mochilas'          => $datos['mochilas'] ?? null,
                'turbos'            => $datos['turbos'] ?? null,
                'usuario_id'        => auth()->id(),
            ]);

            // asociamos los productos a cada fumigacion
            foreach ($request->productos as $producto) {
                $fumigacion->productos()->attach($producto['producto_id'], [
                    'dosis_introducida' => $producto['dosis_introducida'],
                    'cantidad'          => $producto['dosis_introducida'],
                ]);
            }
        }

        // el stock se descuenta una sola vez, independiente del numero de parcelas
        // dosis x turbos/mochilas = total gastado
        $unidades = $datos['metodo_aplicacion'] === 'mochila'
            ? $datos['mochilas']
            : $datos['turbos'];

        foreach ($request->productos as $producto) {
            $totalGastado = $producto['dosis_introducida'] * $unidades;
            $prod = Producto::findOrFail($producto['producto_id']);
            $prod->stock_actual = max(0, $prod->stock_actual - $totalGastado);
            $prod->save();
        }

        return response()->json(['mensaje' => 'Fumigaciones creadas'], 201);
    }

    public function listar(){
        $operaciones = Operacion::with('parcela')->get();
        $fumigaciones = Fumigacion::with(['parcela', 'productos'])->get();
        return response()->json([
            'operaciones' => $operaciones,
            'fumigaciones' => $fumigaciones
        ]);

    }
    public function borrar($id) {
        $fumigacion = Fumigacion::findOrFail($id);
        // detach primero por la tabla intermedia fumigacion_producto
        $fumigacion->productos()->detach();
        $fumigacion->delete();
        return response()->json(['mensaje' => 'Fumigación eliminada correctamente']);
    }

    // devuelve una fumigacion por id para editar
    public function mostrar($id) {
        $fumigacion = Fumigacion::findOrFail($id);
        return response()->json($fumigacion);
    }

    // actualiza los datos de la fumigacion
    public function actualizar(Request $request, $id) {
        $fumigacion = Fumigacion::findOrFail($id);
        $fumigacion->update($request->all());
        return response()->json(['mensaje' => 'Fumigación actualizada']);
    }
}