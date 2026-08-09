<?php

namespace App\Http\Controllers;

use App\Models\CompraProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompraProductoController extends Controller
{
    // El AdminScope del modelo ya filtra por admin_id (todo el que pertenezca
    // a la misma explotación). Antes había además un where('user_id', Auth::id())
    // que dejaba ver solo las compras registradas por el propio usuario logueado:
    // eso es incorrecto para la vista de "Detalle" del almacén, que debe mostrar
    // todas las compras de la explotación, las haya registrado quien las haya registrado.
    public function listar()
    {
        $compras = CompraProducto::with(['producto', 'proveedor', 'user'])
            ->orderByDesc('fecha_compra')
            ->get();
        return response()->json($compras);
    }

   public function crear(Request $request)
    {
        $datos = $request->validate([
            'producto_id'     => ['required', Rule::exists('productos', 'id')->where('admin_id', $request->user()->adminId())],
            'proveedor_id'    => ['required', Rule::exists('proveedores', 'id')->where('admin_id', $request->user()->adminId())],
            'cantidad_compra' => 'required|numeric|min:0',
            'precio'          => 'required|numeric|min:0',
            'fecha_compra'    => 'required|date',
        ]);

        $datos['user_id'] = Auth::id();

        $compra = CompraProducto::create($datos);

        // Precio medio ponderado: mezcla lo que ya había con lo recién comprado
        $producto      = $compra->producto;
        $stockAnterior = (float) $producto->stock_actual;
        $costeAnterior = (float) ($producto->precio ?? 0);
        $cantidad      = (float) $datos['cantidad_compra'];
        $precioCompra  = (float) $datos['precio'];

        $stockTotal = $stockAnterior + $cantidad;

        $producto->precio = $stockTotal > 0
            ? round((($stockAnterior * $costeAnterior) + ($cantidad * $precioCompra)) / $stockTotal, 2)
            : $precioCompra;

        $producto->stock_actual = $stockTotal;
        $producto->save();

        return response()->json(['mensaje' => 'Compra registrada', 'compra' => $compra], 201);
    }

    /**
     * Histórico de compras de un producto (para el detalle), con proveedor
     * cargado y filtros opcionales de rango de fechas y proveedor.
     * GET /api/productos/{id}/compras?desde=&hasta=&proveedor_id=
     */
    public function historial(Request $request, $id)
    {
        // findOrFail respeta el AdminScope: 404 si el producto es de otro admin
        Producto::findOrFail($id);

        $compras = $this->consultaFiltrada($request, $id)
            ->with('proveedor')
            ->orderByDesc('fecha_compra')
            ->get();

        return response()->json($compras);
    }

    /**
     * Indicadores y series agregadas del histórico de compras de un producto,
     * calculados en SQL (sum/avg/min/max/groupBy), no en el front.
     * GET /api/productos/{id}/compras/resumen?desde=&hasta=&proveedor_id=
     */
    public function resumen(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $query = $this->consultaFiltrada($request, $id);

        $agregado = (clone $query)->selectRaw('
            COALESCE(SUM(cantidad_compra * precio), 0) as gasto_total,
            COALESCE(AVG(precio), 0) as precio_medio,
            COALESCE(MIN(precio), 0) as precio_minimo,
            COALESCE(MAX(precio), 0) as precio_maximo,
            COUNT(*) as num_compras
        ')->first();

        $ultimaCompra = (clone $query)
            ->orderByDesc('fecha_compra')
            ->first(['fecha_compra', 'precio']);

        $evolucionPrecio = (clone $query)
            ->orderBy('fecha_compra')
            ->get(['fecha_compra', 'precio'])
            ->map(fn($c) => [
                'fecha'  => $c->fecha_compra->format('Y-m-d'),
                'precio' => (float) $c->precio,
            ]);

        // gasto por mes: respeta el filtro de fechas (útil al acotar a un año)
        $gastoPorMes = (clone $query)
            ->selectRaw("DATE_FORMAT(fecha_compra, '%Y-%m') as mes, SUM(cantidad_compra * precio) as gasto")
            ->groupBy('mes')->orderBy('mes')
            ->get();

        // gasto por año: histórico completo, sin el filtro de fechas, para ver
        // la evolución multi-año aunque la tabla esté acotada a un periodo
        $gastoPorAnio = CompraProducto::where('producto_id', $id)
            ->selectRaw('YEAR(fecha_compra) as anio, SUM(cantidad_compra * precio) as gasto')
            ->groupBy('anio')->orderBy('anio')
            ->get();

        // precio medio por proveedor: respeta fechas pero NO el filtro de
        // proveedor (si no, la comparativa se quedaría con una sola barra).
        // Sin join: el AdminScope global añade "where admin_id" sin prefijo de
        // tabla, y join a proveedores (que también tiene admin_id) lo haría
        // ambiguo en SQL. Se agrega por id y el nombre se resuelve aparte.
        $queryProveedor = CompraProducto::where('producto_id', $id);
        $this->aplicarFiltroFechas($request, $queryProveedor);
        $porProveedorAgg = $queryProveedor
            ->selectRaw('proveedor_id, AVG(precio) as precio_medio')
            ->whereNotNull('proveedor_id')
            ->groupBy('proveedor_id')
            ->get();

        $proveedores = Proveedor::whereIn('id', $porProveedorAgg->pluck('proveedor_id'))->get()->keyBy('id');
        $precioPorProveedor = $porProveedorAgg->map(fn($fila) => [
            'proveedor_id'  => $fila->proveedor_id,
            'proveedor'     => $proveedores[$fila->proveedor_id]->nombre_comercial
                ?: $proveedores[$fila->proveedor_id]->nombre_empresa
                ?: '—',
            'precio_medio'  => round((float) $fila->precio_medio, 2),
        ])->values();

        return response()->json([
            'gasto_total'         => round((float) $agregado->gasto_total, 2),
            'precio_medio'        => round((float) $agregado->precio_medio, 2),
            'precio_minimo'       => round((float) $agregado->precio_minimo, 2),
            'precio_maximo'       => round((float) $agregado->precio_maximo, 2),
            'num_compras'         => (int) $agregado->num_compras,
            'ultima_compra'       => $ultimaCompra ? [
                'fecha'  => $ultimaCompra->fecha_compra->format('Y-m-d'),
                'precio' => (float) $ultimaCompra->precio,
            ] : null,
            'valor_stock_actual'  => round((float) $producto->stock_actual * (float) $producto->precio, 2),
            'evolucion_precio'    => $evolucionPrecio,
            'gasto_por_mes'       => $gastoPorMes,
            'gasto_por_anio'      => $gastoPorAnio,
            'precio_por_proveedor' => $precioPorProveedor,
        ]);
    }

    private function consultaFiltrada(Request $request, $productoId)
    {
        $query = CompraProducto::where('producto_id', $productoId);
        $this->aplicarFiltroFechas($request, $query);

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->query('proveedor_id'));
        }

        return $query;
    }

    private function aplicarFiltroFechas(Request $request, $query)
    {
        if ($request->filled('desde')) {
            $query->whereDate('fecha_compra', '>=', $request->query('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha_compra', '<=', $request->query('hasta'));
        }
    }
}