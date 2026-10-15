<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\CompraProducto;
use App\Models\Operacion;


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
        $gastoPorAnio = CompraProducto::selectRaw(yearSql('fecha_compra') . ' as anio, SUM(cantidad_compra * precio) as gasto')
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

    /**
     * Productos clasificados en "frecuentes" (con consumo este año) y "menos
     * frecuentes" (sin consumo este año). El consumo del año = dosis de las
     * operaciones (abonado) + cantidad de los productos en fumigaciones.
     * GET /api/almacen/clasificado
     */
    public function clasificado(Request $request)
    {
        $anio = now()->year;
        $adminId = $request->user()->adminId();

        // --- consumo de ESTE año, por producto ---
        // operaciones (abonado): producto_id + dosis. El scope filtra por admin.
        $opsAnio = Operacion::whereNotNull('producto_id')
            ->whereRaw(yearSql('hora_inicio') . ' = ?', [$anio])
            ->selectRaw('producto_id, SUM(dosis) as consumo, COUNT(*) as n')
            ->groupBy('producto_id')->get()->keyBy('producto_id');

        // fumigaciones: cantidad del pivote. DB::table no respeta el scope, así
        // que se filtra admin_id de fumigaciones a mano.
        $fumAnio = DB::table('fumigacion_producto as fp')
            ->join('fumigaciones as f', 'f.id', '=', 'fp.fumigacion_id')
            ->where('f.admin_id', $adminId)
            ->whereRaw(yearSql('f.hora_inicio') . ' = ?', [$anio])
            ->selectRaw('fp.producto_id, SUM(fp.cantidad) as consumo, COUNT(*) as n')
            ->groupBy('fp.producto_id')->get()->keyBy('producto_id');

        // --- último uso de SIEMPRE (sirve para "menos frecuentes") ---
        $opsUltimo = Operacion::whereNotNull('producto_id')
            ->selectRaw('producto_id, MAX(hora_inicio) as ultimo')
            ->groupBy('producto_id')->get()->keyBy('producto_id');
        $fumUltimo = DB::table('fumigacion_producto as fp')
            ->join('fumigaciones as f', 'f.id', '=', 'fp.fumigacion_id')
            ->where('f.admin_id', $adminId)
            ->selectRaw('fp.producto_id, MAX(f.hora_inicio) as ultimo')
            ->groupBy('fp.producto_id')->get()->keyBy('producto_id');

        $frecuentes = [];
        $menosFrecuentes = [];

        foreach (Producto::all() as $p) {
            $consumo = (float) (($opsAnio[$p->id]->consumo ?? 0) + ($fumAnio[$p->id]->consumo ?? 0));
            $count   = (int) (($opsAnio[$p->id]->n ?? 0) + ($fumAnio[$p->id]->n ?? 0));

            $ultimos = array_filter([
                $opsUltimo[$p->id]->ultimo ?? null,
                $fumUltimo[$p->id]->ultimo ?? null,
            ]);
            $ultimoUso = $ultimos ? substr(max($ultimos), 0, 10) : null;

            $item = [
                'id'                => $p->id,
                'nombre'            => $p->nombre,
                'categoria'         => $p->categoria,
                'stock'             => (float) $p->stock_actual,
                'minimo'            => (float) $p->stock_minimo,
                'maximo'            => $p->stock_maximo !== null ? (float) $p->stock_maximo : null,
                'unidad'            => $p->unidad,
                'consumo_anual'     => round($consumo, 2),
                'operaciones_count' => $count,
                'ultimo_uso'        => $ultimoUso,
            ];

            if ($consumo > 0) {
                $frecuentes[] = $item;
            } else {
                $menosFrecuentes[] = $item;
            }
        }

        // frecuentes por consumo descendente; menos frecuentes, alfabético
        usort($frecuentes, fn($a, $b) => $b['consumo_anual'] <=> $a['consumo_anual']);
        usort($menosFrecuentes, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

        return response()->json([
            'frecuentes'       => $frecuentes,
            'menos_frecuentes' => $menosFrecuentes,
        ]);
    }

    /**
     * Historial del producto (del año en curso): compras y usos.
     * GET /api/productos/{id}/historial
     */
    public function historial(Request $request, $id)
    {
        $producto = Producto::findOrFail($id); // findOrFail respeta el AdminScope
        $anio = now()->year;

        // compras del año (las borradas quedan fuera por SoftDeletes)
        $compras = CompraProducto::where('producto_id', $id)
            ->whereRaw(yearSql('fecha_compra') . ' = ?', [$anio])
            ->with('proveedor')
            ->orderByDesc('fecha_compra')
            ->get()
            ->map(function ($c) {
                $precio   = $c->precio !== null ? (float) $c->precio : null;
                $cantidad = (float) $c->cantidad_compra;
                return [
                    'fecha'           => $c->fecha_compra->format('Y-m-d'),
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'importe_total'   => $precio !== null ? round($cantidad * $precio, 2) : null,
                    'proveedor'       => $c->proveedor_nombre
                        ?: optional($c->proveedor)->nombre_comercial
                        ?: optional($c->proveedor)->nombre_empresa,
                ];
            });

        // usos del año: operaciones (abonado) + fumigaciones (pivote)
        $usosOp = Operacion::where('producto_id', $id)
            ->whereRaw(yearSql('hora_inicio') . ' = ?', [$anio])
            ->with('parcela:id,poligono,parcela')
            ->get()
            ->map(fn($o) => [
                'fecha'          => substr($o->hora_inicio, 0, 10),
                'parcela'        => $o->parcela ? ['poligono' => $o->parcela->poligono, 'parcela' => $o->parcela->parcela] : null,
                'tipo_operacion' => $o->tipo_operacion,
                'cantidad'       => (float) $o->dosis,
            ]);

        $usosFum = $producto->Fumigacion()
            ->whereRaw(yearSql('fumigaciones.hora_inicio') . ' = ?', [$anio])
            ->with('parcela:id,poligono,parcela')
            ->get()
            ->map(fn($f) => [
                'fecha'          => substr($f->hora_inicio, 0, 10),
                'parcela'        => $f->parcela ? ['poligono' => $f->parcela->poligono, 'parcela' => $f->parcela->parcela] : null,
                'tipo_operacion' => 'fumigacion',
                'cantidad'       => (float) $f->pivot->cantidad,
            ]);

        $usos = $usosOp->concat($usosFum)->sortByDesc('fecha')->values();

        return response()->json([
            'compras' => $compras,
            'usos'    => $usos,
        ]);
    }
}