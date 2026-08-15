<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\Operacion;
use App\Models\Fumigacion;
use App\Models\GastoRiego;
use App\Models\RiegoManta;
use App\Services\CosteFumigacionService; // CAMBIO: import del servicio
use Illuminate\Http\Request;

class GastosController extends Controller
{
    // CAMBIO: inyección del servicio de cálculo
    public function __construct(private CosteFumigacionService $coste) {}

    public function resumen(Request $request)
    {
        $campania = $request->query('campana'); // "2026" o "todas"

        // el scope global ya filtra por admin
        $parcelas    = Parcela::with('explotacion:id,nombre')->get();
        $operaciones = Operacion::with(['parcela', 'producto'])->get();
        $fumigaciones = Fumigacion::with(['parcela', 'productos'])->get();
        // riego: gastos_riego (goteo/mantenimiento) + riegos_manta (introducidos
        // desde el nuevo componente de Riego); aquí solo se LEEN como detalle,
        // la introducción de datos vive en RiegoController/RiegoMantaController
        $gastosRiego = GastoRiego::all();
        $riegosManta = RiegoManta::all();

        // filtro por campaña (año)
        if ($campania && $campania !== 'todas') {
            $operaciones = $operaciones->filter(fn($o) => str_starts_with((string) $o->hora_inicio, $campania))->values();
            $fumigaciones = $fumigaciones->filter(fn($f) => str_starts_with((string) $f->hora_inicio, $campania))->values();
            $gastosRiego = $gastosRiego->filter(fn($g) => (string) $g->anio === (string) $campania)->values();
            $riegosManta = $riegosManta->filter(fn($r) => str_starts_with((string) $r->fecha, $campania))->values();
        }

        // enriquezco las fumigaciones con las hanegadas del lote (igual que TareasController)
        $fumigaciones = $this->coste->enriquecerHanegadas($fumigaciones);

        $porParcela = $parcelas->map(fn($p) => $this->resumenParcela($p, $operaciones, $fumigaciones, $gastosRiego, $riegosManta));

        $porExplotacion = $porParcela
            ->groupBy('explotacion')
            ->map(fn($parcelasExplo, $nombre) => [
                'nombre' => $nombre,
                'coste'  => round($parcelasExplo->sum('costeTotal'), 2),
            ])
            ->values();

        return response()->json([
            'porExplotacion' => $porExplotacion,
            'porParcela'     => $porParcela->values(),
        ]);
    }

    private function resumenParcela($parcela, $operaciones, $fumigaciones, $gastosRiego, $riegosManta)
    {
        $opsParcela = $operaciones->filter(fn($o) => $o->parcela_id === $parcela->id);
        $fumsParcela = $fumigaciones->filter(fn($f) => $f->parcela_id === $parcela->id);

        // operaciones agrupadas por tipo; cada operación individual se conserva como su propia fila
        $agrupadas = [];
        foreach ($opsParcela as $op) {
            $agrupadas[$op->tipo_operacion][] = [
                'operario'        => $op->operario,
                'fecha'           => $this->formatearFecha($op->hora_inicio),
                'horas'           => (float) ($op->duracion_minutos ?? 0) / 60,
                'precio'          => (float) ($op->precio ?? 0), // total: mano de obra + material
                'precioMaterial'  => (float) ($op->precio_material ?? 0),
                // solo relevante en abonado: qué producto y cuánta dosis se aplicó
                'producto' => ($op->tipo_operacion === 'abonado' && $op->producto)
                    ? [
                        'nombre' => $op->producto->nombre,
                        'dosis'  => (float) ($op->dosis ?? 0),
                        'unidad' => $op->producto->unidad ?? '',
                    ]
                    : null,
            ];
        }
        $operacionesSalida = [];
        foreach ($agrupadas as $tipo => $entradas) {
            // precio de mano de obra de cada fila = precio total de la operación menos el material aplicado
            $filas = array_map(fn($e) => [
                'operario' => $e['operario'],
                'fecha'    => $e['fecha'],
                'horas'    => round($e['horas'], 1),
                'precio'   => round($e['precio'] - $e['precioMaterial'], 2),
            ], $entradas);

            $precioTipo = array_sum(array_column($filas, 'precio'));

            // abonado: una fila de material por cada operación con producto, con SOLO su coste de material
            $materiales = [];
            foreach ($entradas as $e) {
                if ($e['producto']) {
                    $materiales[] = [
                        'fecha'  => $e['fecha'],
                        'nombre' => $e['producto']['nombre'],
                        'dosis'  => $e['producto']['dosis'],
                        'unidad' => $e['producto']['unidad'],
                        'coste'  => $e['precioMaterial'],
                    ];
                } elseif ($e['precioMaterial'] > 0) {
                    // mantenimiento (y cualquier otro tipo sin producto de almacén): el material
                    // lo escribe el usuario a mano, sin producto ni dosis asociados
                    $materiales[] = [
                        'fecha'  => $e['fecha'],
                        'nombre' => 'Material',
                        'dosis'  => null,
                        'unidad' => '',
                        'coste'  => $e['precioMaterial'],
                    ];
                }
            }

            $operacionesSalida[] = [
                'tipo'       => $tipo,
                'precioTipo' => round($precioTipo, 2),
                'filas'      => $filas,
                'materiales' => $materiales,
            ];
        }

        $tractor = $this->fumigacionesMetodo($fumsParcela, 'tractor');
        $mochila = $this->fumigacionesMetodo($fumsParcela, 'mochila');

        $costeOps = $opsParcela->sum(fn($o) => (float) ($o->precio ?? 0));
        $costeFums = collect($tractor)->sum('precio') + collect($mochila)->sum('precio');
        $costeMaterial = collect($tractor)->sum('costeMaterial') + collect($mochila)->sum('costeMaterial');

        $impMunicipal = (float) ($parcela->impuesto_municipal ?? 0);
        $impCequiaje  = (float) ($parcela->impuesto_cequiaje ?? 0);
        $impuestosTotal = $impMunicipal + $impCequiaje;

        $riegoParcela = $gastosRiego->filter(fn($g) => $g->parcela_id === $parcela->id)
            ->map(fn($g) => ['concepto' => $g->concepto, 'importe' => (float) $g->importe, 'fecha' => null])
            ->concat(
                $riegosManta->filter(fn($r) => $r->parcela_id === $parcela->id)
                    ->map(fn($r) => [
                        'concepto' => 'manta',
                        'importe' => (float) $r->importe,
                        'fecha' => $this->formatearFecha($r->fecha),
                    ])
            )
            ->values();
        $riegoTotal = $riegoParcela->sum('importe');

        $costeTotal = $costeOps + $costeFums + $costeMaterial + $impuestosTotal + $riegoTotal;

        return [
            'id'          => $parcela->id,
            'nombre'      => $parcela->nombre ?: ('Pol. ' . $parcela->poligono . ' - Par. ' . $parcela->parcela),
            'explotacion' => $parcela->explotacion->nombre ?? 'Sin explotación',
            'variedad'    => $parcela->variedad,
            'operaciones' => $operacionesSalida,
            'fumigacionesTractor' => $tractor,
            'fumigacionesMochila' => $mochila,
            'impuestos'   => [
                'municipal' => $impMunicipal,
                'cequiaje'  => $impCequiaje,
                'total'     => round($impuestosTotal, 2),
            ],
            'riego'       => $riegoParcela,
            'riegoTotal'  => round($riegoTotal, 2),
            'costeTotal'  => round($costeTotal, 2),
        ];
    }

    private function fumigacionesMetodo($fumsParcela, $metodo)
    {
        $salida = [];
        foreach ($fumsParcela->filter(fn($f) => $f->metodo_aplicacion === $metodo) as $fum) {
            $unidades = $metodo === 'tractor' ? $fum->turbos : $fum->mochilas;
            $proporcion = $this->coste->calcularProporcion($fum);

            $productos = [];
            $costeMaterial = 0;
            foreach ($fum->productos as $prod) {
                $cantidad = $prod->pivot->dosis_introducida * $unidades * $proporcion;
                $precioUnidad = (float) ($prod->pivot->precio ?? $prod->precio ?? 0);
                $coste = $cantidad * $precioUnidad;
                $costeMaterial += $coste;
                $productos[] = [
                    'nombre'   => $prod->nombre,
                    'cantidad' => round($cantidad, 2),
                    'unidad'   => $prod->unidad,
                    'dosis'    => $prod->pivot->dosis_introducida,
                    'coste'    => round($coste, 2),
                ];
            }

            // CAMBIO: el tractor se calcula por hanegadas; la mochila sigue usando el precio guardado
            $precioOperacion = $metodo === 'tractor'
                ? round($this->coste->costeTractorParcela($fum), 2)
                : round((float) $fum->precio, 2);

            $salida[] = [
                'id'        => $fum->id,
                'fecha'     => $this->formatearFecha($fum->hora_inicio),
                'unidades'  => $unidades,
                'operario'  => $fum->operario,
                'hanegadas' => $fum->hanegadas_parcela,
                'litros'    => round($this->coste->calcularLitros($fum), 0),
                'precio'    => $precioOperacion,
                'duracion_minutos' => $fum->duracion_minutos,
                'productos' => $productos,
                'costeMaterial' => round($costeMaterial, 2),
                // coste de operación + todos los productos: única fuente de verdad
                // para la fila "Total" que pinta el front bajo cada fumigación
                'total'     => round($precioOperacion + $costeMaterial, 2),
            ];
        }
        return $salida;
    }

    private function formatearFecha($fechaStr)
    {
        return (new \DateTime($fechaStr))->format('d/m');
    }
}