<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\CompraProducto;


class AlmacenController extends Controller
{
    public function crear(Request $request){

        $producto = $request->validate([
            'nombre' => 'required|max:50',
            'materia_activa' => 'required',
            'ubicacion' => 'required',
            'stock_minimo' => 'required',
            'precio' => 'nullable|numeric|min:0',
        ]);

        Producto::create($producto);

        return response()->json(['mensaje' => 'Producto creado en el almacén'], 201);
    }

    // Info stock bajo dashboard
    public function stockBajo(){
        $productos = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->select('id', 'nombre', 'stock_actual', 'stock_minimo', 'unidad')
            ->get();
        return response()->json($productos);
    }

    /**
     * Vista general del almacén: gasto total en compras por año y los
     * productos en los que más se ha gastado (opcionalmente acotado a un año).
     * GET /api/almacen/resumen?anio=2026
     */
    public function resumenGeneral(Request $request)
    {
        $gastoPorAnio = CompraProducto::selectRaw('YEAR(fecha_compra) as anio, SUM(cantidad_compra * precio) as gasto')
            ->groupBy('anio')->orderBy('anio')
            ->get();

        // sin join: el AdminScope global añade "where admin_id" sin prefijo de
        // tabla, y join a productos (que también tiene admin_id) lo haría
        // ambiguo en SQL. Se agrega por producto_id y el nombre se resuelve aparte.
        $topAgg = CompraProducto::query()
            ->when($request->filled('anio'), fn($q) => $q->whereYear('fecha_compra', $request->query('anio')))
            ->selectRaw('producto_id, SUM(cantidad_compra * precio) as gasto')
            ->groupBy('producto_id')
            ->orderByDesc('gasto')
            ->limit(10)
            ->get();

        $productos = Producto::whereIn('id', $topAgg->pluck('producto_id'))->get()->keyBy('id');

        $topProductos = $topAgg->map(fn($fila) => [
            'producto_id' => $fila->producto_id,
            'nombre'      => $productos[$fila->producto_id]->nombre ?? '—',
            'gasto'       => round((float) $fila->gasto, 2),
        ])->values();

        return response()->json([
            'gasto_por_anio' => $gastoPorAnio,
            'top_productos'  => $topProductos,
        ]);
    }
}