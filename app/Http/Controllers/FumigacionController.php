<?php
namespace App\Http\Controllers;

use App\Models\Fumigacion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FumigacionController extends Controller
{
    public function añadirFumigacion(Request $request)
    {
        $datos = $request->validate([
            'parcela_ids'       => 'required|array',
            'parcela_ids.*'     => ['required', Rule::exists('parcelas', 'id')->where('admin_id', $request->user()->adminId())],
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
            'productos.*.producto_id'       => ['required', Rule::exists('productos', 'id')->where('admin_id', $request->user()->adminId())],
            'productos.*.dosis_introducida' => 'required|numeric|min:0',
        ]);

        $numParcelas = count($datos['parcela_ids']);

        // En tractor el coste se calcula por hanegadas en el resumen, no se reparte aquí.
        // El reparto equitativo del precio total se mantiene solo para mochila.
        $precioPorParcela = $datos['metodo_aplicacion'] === 'mochila'
            ? round($datos['precio'] / $numParcelas, 2)
            : null;

        $unidades = $datos['metodo_aplicacion'] === 'mochila'
            ? $datos['mochilas']
            : $datos['turbos'];

        // comprobamos el stock de TODOS los productos antes de crear nada:
        // si uno falla, no queremos fumigaciones ya creadas con productos a medias
        foreach ($request->productos as $producto) {
            $prod = Producto::find($producto['producto_id']);
            $totalGastado = $producto['dosis_introducida'] * $unidades;
            if (!$prod || $prod->stock_actual < $totalGastado) {
                return response()->json([
                    'mensaje' => 'No hay stock suficiente para registrar esta fumigación.',
                    'errors'  => [
                        'productos' => [
                            'Stock de "' . ($prod->nombre ?? 'producto') . '": ' . ($prod->stock_actual ?? 0) . ' ' . ($prod->unidad ?? '')
                                . '. Necesario: ' . $totalGastado . '.',
                        ],
                    ],
                ], 422);
            }
        }

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

        foreach ($request->productos as $producto) {
            $totalGastado = $producto['dosis_introducida'] * $unidades;
            $prod = Producto::findOrFail($producto['producto_id']);
            $prod->descontarStock((float) $totalGastado);
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
        // eager load: coste_operacion/total/desglose_productos leen la
        // relación 'productos', si no se precarga sale una query por producto
        $fumigacion = Fumigacion::with('productos')->findOrFail($id);
        return response()->json($fumigacion);
    }

    public function actualizar(Request $request, $id)
    {
        $fumigacion = Fumigacion::findOrFail($id);

        $datos = $request->validate([
            'metodo_aplicacion' => 'required|in:tractor,mochila',
            'hora_inicio'       => 'required',
            'descripcion'       => 'required',
            'precio'            => 'nullable|numeric|min:0',
            'precio_turbo'      => 'nullable|numeric|min:0',
            'operario'          => 'nullable|string',
            'duracion_minutos'  => 'nullable|integer|min:0',
            'mochilas'          => 'nullable',
            'litros_agua'       => 'nullable|numeric|min:0',
            'turbos'            => 'nullable',
            'estado'            => 'sometimes|in:pendiente,realizada,revisada',
        ]);

        $fumigacion->update($datos);
        return response()->json(['mensaje' => 'Fumigación actualizada']);
    }
}