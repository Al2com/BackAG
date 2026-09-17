<?php
namespace App\Http\Controllers;

use App\Models\Operacion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperacionController extends Controller
{
    public function crearOperacion(Request $request){
        $operacion = $request->validate([
            'parcela_id'       => ['required', Rule::exists('parcelas', 'id')->where('admin_id', $request->user()->adminId())],
            'operario'         => 'required',
            'tipo_operacion'   => 'required',
            'hora_inicio'      => 'required',
            'duracion_minutos' => 'required',
            'precio'           => 'required',
            'descripcion'      => 'required',
            // solo aplican a abonado: producto consumido y dosis (la dosis ya es el total gastado)
            'producto_id'      => ['nullable', 'required_if:tipo_operacion,abonado', Rule::exists('productos', 'id')->where('admin_id', $request->user()->adminId())],
            'dosis'            => 'nullable|required_if:tipo_operacion,abonado|numeric|min:0.01',
            // parte de 'precio' que corresponde solo a material (el resto es mano de obra);
            // la manda el front ya calculada, aquí solo se persiste para poder desglosarla en Gastos
            'precio_material'  => 'nullable|numeric|min:0',
        ]);

        // abonado: no se crea la operación si no hay stock suficiente del producto
        if ($operacion['tipo_operacion'] === 'abonado') {
            $producto = Producto::find($operacion['producto_id']);
            if (!$producto || $producto->stock_actual < $operacion['dosis']) {
                return response()->json([
                    'mensaje' => 'No hay stock suficiente para registrar esta operación.',
                    'errors'  => [
                        'dosis' => ['Stock disponible: ' . ($producto->stock_actual ?? 0) . ' ' . ($producto->unidad ?? '') . '. Necesario: ' . $operacion['dosis'] . '.'],
                    ],
                ], 422);
            }
        }

        $operacion['usuario_id'] = auth()->id(); // ya estaba correcto, se eliminó usuario_id de la validación
        $nuevaOperacion = Operacion::create($operacion);

        // abonado descuenta del almacén: la dosis introducida en el formulario ya es el consumo total
        if ($nuevaOperacion->tipo_operacion === 'abonado' && $nuevaOperacion->producto_id) {
            $producto = Producto::find($nuevaOperacion->producto_id);
            $producto?->descontarStock((float) $nuevaOperacion->dosis);
        }

        return response()->json(['mensaje' => 'Operación creada'], 201);
    }

    // Contador del panel. El aislamiento lo hace el scope global por admin
    // (PerteneceAdmin), asi que cuenta todas las operaciones de la explotacion
    // y no solo las del usuario logueado, igual que Gastos y Analisis.
    // El listado de la pantalla de operaciones vive en TareasController::listar
    public function contar()
    {
        return response()->json(['total' => Operacion::count()]);
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

        $datos = $request->validate([
            'parcela_id'       => ['required', Rule::exists('parcelas', 'id')->where('admin_id', $request->user()->adminId())],
            'operario'         => 'required',
            'tipo_operacion'   => 'required',
            'hora_inicio'      => 'required',
            'duracion_minutos' => 'required|integer|min:0',
            'precio'           => 'required|numeric|min:0',
            'descripcion'      => 'required',
            'estado'           => 'sometimes|in:pendiente,realizada,revisada',
        ]);

        $operacion->update($datos);
        return response()->json(['mensaje' => 'Operación actualizada']);
    }
}