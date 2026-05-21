<?php
namespace App\Http\Controllers;
use App\Models\Fumigacion;
use App\Models\Producto;
use Illuminate\Http\Request;

class FumigacionController extends Controller {

    public function añadirFumigacion(Request $request) {

        $datos = $request->validate([
            'parcela_id'        => 'required',
            'metodo_aplicacion' => 'required',
            'hora_inicio'       => 'required',
            'descripcion'       => 'required',
            'precio'            => 'required',
            // si es mochila
            'operario'          => 'required_if:metodo_aplicacion,mochila',
            'duracion_minutos'  => 'required_if:metodo_aplicacion,mochila',
            'mochilas'          => 'required_if:metodo_aplicacion,mochila',
            //  si es tractor
            'turbos'            => 'required_if:metodo_aplicacion,tractor',
            'productos'         => 'required|array',
        ]);

        // 1 Crea la fumigacion
        $fumigacion = Fumigacion::create([
            'parcela_id'        => $datos['parcela_id'],
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

        // 2. Recorremos cada productos dl formulario
        foreach ($request->productos as $producto) {

            // Guardamos el producto en la tabla intermedia fumigacion_producto
            $fumigacion->productos()->attach($producto['producto_id'], [
                'dosis_introducida' => $producto['dosis_introducida'],
                'cantidad'          => $producto['dosis_introducida'],
            ]);

            // 3 Calculamos cuanto producto se ha gastado en total
            // mochila: dosis x numero de mochilas | tractor: dosis x numero de turbos
            $unidades = $datos['metodo_aplicacion'] === 'mochila'
                ? $datos['mochilas']
                : $datos['turbos'];

            $totalGastado = $producto['dosis_introducida'] * $unidades;

            // 4. Buscamos el producto y restamos del stock
            // max(0,...) para que nunca quede en negativo
            $prod = Producto::findOrFail($producto['producto_id']);
            $prod->stock_actual = max(0, $prod->stock_actual - $totalGastado);
            $prod->save();
        }

        return response()->json(['mensaje' => 'Fumigación creada'], 201);
    }

    public function listar() {
        $fumigaciones = Fumigacion::all();
        $totalFumigaciones = $fumigaciones->count();
        return response()->json([
            'fumigaciones'      => $fumigaciones,
            'totalFumigaciones' => $totalFumigaciones
        ]);
    }

    public function borrar($id) {
        $fumigacion = Fumigacion::findOrFail($id);
        // Primero detach porque tiene tabla intermedia con productos
        // Si no Laravel da error de clave foranea
        $fumigacion->productos()->detach();
        $fumigacion->delete();
        return response()->json(['mensaje' => 'Fumigación eliminada correctamente']);
    }

      // Devuelve una fumigacion por id para editar
    public function mostrar($id) {
        $fumigacion = Fumigacion::findOrFail($id);
        return response()->json($fumigacion);
    }

    // Actualiza los datos de la fumigacion
    public function actualizar(Request $request, $id) {
        $fumigacion = Fumigacion::findOrFail($id);
        $fumigacion->update($request->all());
        return response()->json(['mensaje' => 'Fumigación actualizada']);
    }
}