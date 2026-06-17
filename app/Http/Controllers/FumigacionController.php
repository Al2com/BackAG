<?php
namespace App\Http\Controllers;

use App\Models\Fumigacion;
use App\Models\Producto;
use Illuminate\Http\Request;

class FumigacionController extends Controller
{
    public function añadirFumigacion(Request $request)
    {
        $datos = $request->validate([
            'parcela_ids'       => 'required|array',
            'parcela_ids.*'     => 'required|exists:parcelas,id',
            'metodo_aplicacion' => 'required',
            'hora_inicio'       => 'required',
            'descripcion'       => 'required',
            'precio'            => 'required_if:metodo_aplicacion,mochila', // el precio total solo aplica a mochila
            'precio_turbo'      => 'required_if:metodo_aplicacion,tractor|numeric|min:0', // precio por turbo en tractor
            'operario'          => 'required_if:metodo_aplicacion,mochila',
            'duracion_minutos'  => 'required_if:metodo_aplicacion,mochila',
            'mochilas'          => 'required_if:metodo_aplicacion,mochila',
            'litros_agua'       => 'nullable|numeric|min:0',
            'turbos'            => 'required_if:metodo_aplicacion,tractor',
            'productos'         => 'required|array',
            'productos.*.producto_id'       => 'required|exists:productos,id',
            'productos.*.dosis_introducida' => 'required|numeric|min:0',
        ]);

        $numParcelas = count($datos['parcela_ids']);

        // En tractor el coste se calcula por hanegadas en el resumen, no se reparte aquí.
        // El reparto equitativo del precio total se mantiene solo para mochila.
        $precioPorParcela = $datos['metodo_aplicacion'] === 'mochila'
            ? round($datos['precio'] / $numParcelas, 2)
            : null;

        foreach ($datos['parcela_ids'] as $parcelaId) {
            $fumigacion = Fumigacion::create([
                'parcela_id'        => $parcelaId,
                'metodo_aplicacion' => $datos['metodo_aplicacion'],
                'hora_inicio'       => $datos['hora_inicio'],
                'descripcion'       => $datos['descripcion'],
                'precio'            => $precioPorParcela,
                'precio_turbo'      => $datos['metodo_aplicacion'] === 'tractor' ? $datos['precio_turbo'] : null,
                'num_parcelas'      => $numParcelas,
                'operario'          => $datos['operario'] ?? null,
                'duracion_minutos'  => $datos['duracion_minutos'] ?? null,
                'mochilas'          => $datos['mochilas'] ?? null,
                'litros_agua'       => $datos['metodo_aplicacion'] === 'mochila' ? ($datos['litros_agua'] ?? null) : null,
                'turbos'            => $datos['turbos'] ?? null,
                'usuario_id'        => auth()->id(),
            ]);

            foreach ($request->productos as $producto) {
                $prod = Producto::find($producto['producto_id']);
                $fumigacion->productos()->attach($producto['producto_id'], [
                    'dosis_introducida' => $producto['dosis_introducida'],
                    'cantidad'          => $producto['dosis_introducida'],
                    'precio'            => $prod?->precio, // coste del momento, congelado
                ]);
            }
        }

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

    // Filtra por usuario_id para que cada usuario solo vea sus fumigaciones
    public function listar()
    {
        $fumigaciones = Fumigacion::where('usuario_id', auth()->id())
            ->with(['parcela', 'productos'])
            ->get();

        $fumigaciones->each(function ($fum) {
            $parcelaIds = Fumigacion::where('usuario_id', auth()->id())
                ->where('hora_inicio', $fum->hora_inicio)
                ->where('metodo_aplicacion', $fum->metodo_aplicacion)
                ->where('turbos', $fum->turbos)
                ->pluck('parcela_id')
                ->unique();

            $totalHanegadas = \App\Models\Parcela::whereIn('id', $parcelaIds)
                ->sum('dimension_hanegadas');

            $fum->total_hanegadas = floatval($totalHanegadas);
            $fum->hanegadas_parcela = floatval($fum->parcela->dimension_hanegadas ?? 0);
        });

        return response()->json($fumigaciones);
    }

    public function borrar($id)
    {
        $fumigacion = Fumigacion::findOrFail($id);
        $fumigacion->productos()->detach();
        $fumigacion->delete();
        return response()->json(['mensaje' => 'Fumigación eliminada correctamente']);
    }

    public function mostrar($id)
    {
        $fumigacion = Fumigacion::findOrFail($id);
        return response()->json($fumigacion);
    }

    public function actualizar(Request $request, $id)
    {
        $fumigacion = Fumigacion::findOrFail($id);
        $fumigacion->update($request->all());
        return response()->json(['mensaje' => 'Fumigación actualizada']);
    }
}