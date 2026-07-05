<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\Operacion;
use App\Models\Fumigacion;
use App\Models\GastoRiego;
use App\Services\CosteFumigacionService; // CAMBIO: import del servicio
use Illuminate\Http\Request;

class GastosController extends Controller
{
    private const LITROS_TRACTOR = 1500;
    private const LITROS_MOCHILA = 12;

    // CAMBIO: inyección del servicio de cálculo
    public function __construct(private CosteFumigacionService $coste) {}

    public function resumen(Request $request)
    {
        $campania = $request->query('campana'); // "2026" o "todas"

        // el scope global ya filtra por admin
        $parcelas    = Parcela::with('explotacion:id,nombre')->get();
        $operaciones = Operacion::with(['parcela', 'producto'])->get();
        $fumigaciones = Fumigacion::with(['parcela', 'productos'])->get();
        $gastosRiego = GastoRiego::all();

        // filtro por campaña (año)
        if ($campania && $campania !== 'todas') {
            $operaciones = $operaciones->filter(fn($o) => str_starts_with((string) $o->hora_inicio, $campania))->values();
            $fumigaciones = $fumigaciones->filter(fn($f) => str_starts_with((string) $f->hora_inicio, $campania))->values();
            $gastosRiego = $gastosRiego->filter(fn($g) => (string) $g->anio === (string) $campania)->values();
        }

        // enriquezco las fumigaciones con las hanegadas del lote (igual que TareasController)
        $fumigaciones->each(function ($fum) use ($fumigaciones) {
            $hermanas = $fumigaciones->filter(fn($f) =>
                $f->hora_inicio === $fum->hora_inicio &&
                $f->metodo_aplicacion === $fum->metodo_aplicacion &&
                $f->turbos === $fum->turbos
            );
            $fum->total_hanegadas = $hermanas->sum(fn($f) => (float) ($f->parcela->dimension_hanegadas ?? 0));
            $fum->hanegadas_parcela = (float) ($fum->parcela->dimension_hanegadas ?? 0);
        });

        $porParcela = $parcelas->map(fn($p) => $this->resumenParcela($p, $operaciones, $fumigaciones, $gastosRiego));

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

    private function calcularLitros($fum)
    {
        $unidades = $fum->metodo_aplicacion === 'tractor' ? $fum->turbos : $fum->mochilas;
        $litrosPorUnidad = $fum->metodo_aplicacion === 'tractor' ? self::LITROS_TRACTOR : self::LITROS_MOCHILA;
        $litrosTotales = $unidades * $litrosPorUnidad;

        if ($fum->metodo_aplicacion === 'tractor' && $fum->total_hanegadas > 0) {
            return $litrosTotales * ($fum->hanegadas_parcela / $fum->total_hanegadas);
        }
        return $litrosTotales / ($fum->num_parcelas ?: 1);
    }

    private function calcularProporcion($fum)
    {
        if ($fum->metodo_aplicacion === 'tractor' && $fum->total_hanegadas > 0) {
            return $fum->hanegadas_parcela / $fum->total_hanegadas;
        }
        return 1 / ($fum->num_parcelas ?: 1);
    }

    private function resumenParcela($parcela, $operaciones, $fumigaciones, $gastosRiego)
    {
        $opsParcela = $operaciones->filter(fn($o) => $o->parcela_id === $parcela->id);
        $fumsParcela = $fumigaciones->filter(fn($f) => $f->parcela_id === $parcela->id);

        // operaciones agrupadas por tipo y operario
        $agrupadas = [];
        foreach ($opsParcela as $op) {
            $agrupadas[$op->tipo_operacion][$op->operario][] = [
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
        foreach ($agrupadas as $tipo => $porOperario) {
            $precioTipo = 0; // acumula solo la parte de mano de obra (el total menos el material)
            $operariosSalida = [];
            $materiales = [];
            foreach ($porOperario as $operario => $entradas) {
                $horas = array_sum(array_column($entradas, 'horas'));
                $totalOperario = array_sum(array_column($entradas, 'precio'));
                $materialOperario = array_sum(array_column($entradas, 'precioMaterial'));
                $precioTipo += ($totalOperario - $materialOperario);
                $detalle = implode(' · ', array_map(
                    fn($e) => $e['fecha'] . ' ' . number_format($e['horas'], 1) . 'h',
                    $entradas
                ));
                $operariosSalida[] = [
                    'operario' => $operario,
                    'horas'    => round($horas, 1),
                    'detalle'  => $detalle,
                ];

                // abonado: una fila de material por cada operación con producto, con SOLO su coste de material
                foreach ($entradas as $e) {
                    if ($e['producto']) {
                        $materiales[] = [
                            'fecha'  => $e['fecha'],
                            'nombre' => $e['producto']['nombre'],
                            'dosis'  => $e['producto']['dosis'],
                            'unidad' => $e['producto']['unidad'],
                            'coste'  => $e['precioMaterial'],
                        ];
                    }
                }
            }
            $operacionesSalida[] = [
                'tipo'       => $tipo,
                'precioTipo' => round($precioTipo, 2),
                'operarios'  => $operariosSalida,
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
            ->map(fn($g) => ['concepto' => $g->concepto, 'importe' => (float) $g->importe])
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
            $proporcion = $this->calcularProporcion($fum);

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

            $salida[] = [
                'id'        => $fum->id,
                'fecha'     => $this->formatearFecha($fum->hora_inicio),
                'unidades'  => $unidades,
                'operario'  => $fum->operario,
                'hanegadas' => $fum->hanegadas_parcela,
                'litros'    => round($this->calcularLitros($fum), 0),
                // CAMBIO: el tractor se calcula por hanegadas; la mochila sigue usando el precio guardado
                'precio'    => $metodo === 'tractor'
                    ? round($this->coste->costeTractorParcela($fum), 2)
                    : round((float) $fum->precio, 2),
                'duracion_minutos' => $fum->duracion_minutos,
                'productos' => $productos,
                'costeMaterial' => round($costeMaterial, 2),
            ];
        }
        return $salida;
    }

    private function formatearFecha($fechaStr)
    {
        return (new \DateTime($fechaStr))->format('d/m');
    }
}