<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\CompraProducto;

class ProductoController extends Controller
{
    public function mostrarProductos(){
        $productos=Producto::select(
        'id',    
        'nombre',
        'materia_activa',
        'precio',
        'ubicacion',
        'dosis_recomendada',
        'stock_actual',
        'unidad')->get();
        return response()->json($productos);

    }

      //para sacar el producto a modificar GET /api/productos/lista/{id}
    public function modificarProducto($id){
        $producto = Producto::findOrFail($id);
        return response()->json($producto);
    }

    // actualiza el producto elegido por id PUT /api/productos/lista/{id}
    public function actualizarProducto(Request $request, $id){
        $producto = Producto::findOrFail($id);

        // Validamos y de paso evitamos guardar campos basura (created_at, id...)
        // y que un precio vacío o fuera de rango tumbe el servidor con un 500.
        $datos = $request->validate([
            'nombre'            => 'required|max:50',
            'materia_activa'    => 'required',
            'ubicacion'         => 'required',
            'precio'            => 'nullable|numeric|min:0|max:9999.99', // columna decimal(6,2)
            'stock_minimo'      => 'required|integer|min:0',
            'stock_actual'      => 'nullable|integer|min:0',
            'dosis_recomendada' => 'nullable|numeric|min:0',
            'unidad'            => 'nullable',
        ]);

        $producto->update($datos);
        return response()->json($producto);
    }

    // borra un producto DELETE /api/productos/lista/{id}
    // findOrFail respeta el AdminScope: no se puede borrar el producto de otro admin
    public function eliminarProducto($id){
        $producto = Producto::findOrFail($id);

        // No permitimos borrar un producto con historial. La pivote
        // fumigacion_producto está en cascadeOnDelete, así que borrarlo
        // arrastraría el coste de material de fumigaciones pasadas y
        // falsearía Gastos. Las compras quedarían huérfanas (producto_id null).
        $enFumigaciones = $producto->Fumigacion()->exists();
        $enCompras = CompraProducto::where('producto_id', $id)->exists();

        if ($enFumigaciones || $enCompras) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el producto tiene compras o fumigaciones asociadas.'
            ], 409);
        }

        $producto->delete();
        return response()->json(['mensaje' => 'Producto eliminado correctamente']);
    }
}