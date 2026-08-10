<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\Operacion;
use App\Models\Fumigacion;
use App\Models\Recoleccion;
use App\Models\GastoRiego;
use App\Services\CosteFumigacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalisisController extends Controller
{
    public function __construct(private CosteFumigacionService $coste) {}

    /**
     * Rango [inicio, fin] a partir de fecha_inicio/fecha_fin, o del año
     * completo (anio, por defecto el actual) si no se pasa un rango explícito.
     */
    private function rangoFechas(Request $request): array
    {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        if ($fechaInicio && $fechaFin) {
            return [Carbon::parse($fechaInicio)->startOfDay(), Carbon::parse($fechaFin)->endOfDay()];
        }

        $anio = (int) $request->query('anio', now()->year);
        return [Carbon::create($anio, 1, 1)->startOfDay(), Carbon::create($anio, 12, 31)->endOfDay()];
    }

    /**
     * Gasto por hanegada, litros y dosis de producto aplicados, diferenciados
     * por método de aplicación (tractor / mochila). Si se filtra por parcela,
     * los importes son solo la parte imputada a esa parcela (los pases de
     * tractor/mochila que cubren varias parcelas a la vez se reparten por
     * hanegadas, igual que en Gastos); si no, se agregan todas las parcelas
     * del admin autenticado.
     */
    public function costesPorMetodo(Request $request)
    {
        [$inicio, $fin] = $this->rangoFechas($request);
        $parcelaId = $request->query('parcela_id');

        // el scope global ya filtra por admin: si la parcela no es suya, 404
        $parcela = $parcelaId ? Parcela::findOrFail($parcelaId) : null;
        $parcelas = $parcela ? collect([$parcela]) : Parcela::all();
        $hanegadasTotales = $parcelas->sum(fn($p) => (float) $p->dimension_hanegadas);

        // se cargan TODAS las fumigaciones del periodo (no solo las de la
        // parcela filtrada) porque el reparto por hanegadas de un pase de
        // tractor necesita conocer a todas las parcelas hermanas del lote
        $fumigaciones = Fumigacion::with(['parcela', 'productos'])
            ->whereBetween('hora_inicio', [$inicio, $fin])
            ->get();
        $fumigaciones = $this->coste->enriquecerHanegadas($fumigaciones);

        $fumsEnFoco = $parcela
            ? $fumigaciones->filter(fn($f) => $f->parcela_id === $parcela->id)->values()
            : $fumigaciones;

        $metodos = [];
        foreach (['tractor', 'mochila'] as $metodo) {
            $fumsMetodo = $fumsEnFoco->filter(fn($f) => $f->metodo_aplicacion === $metodo)->values();

            $costeManoObra = $fumsMetodo->sum(fn($f) => $this->coste->costeOperacionParcela($f));
            $costeMaterial = $fumsMetodo->sum(fn($f) => $this->coste->costeMaterialParcela($f));
            $costeTotal = $costeManoObra + $costeMaterial;
            $litros = $fumsMetodo->sum(fn($f) => $this->coste->calcularLitros($f));

            $metodos[$metodo] = [
                'costeTotal' => round($costeTotal, 2),
                'costeManoObra' => round($costeManoObra, 2),
                'costeMaterial' => round($costeMaterial, 2),
                'gastoPorHanegada' => $hanegadasTotales > 0 ? round($costeTotal / $hanegadasTotales, 2) : null,
                'litros' => round($litros, 0),
                'numFumigaciones' => $fumsMetodo->count(),
                'productos' => $this->coste->productosPorFumigaciones($fumsMetodo),
            ];
        }

        return response()->json([
            'parcela' => $parcela ? [
                'id' => $parcela->id,
                'nombre' => $parcela->nombre ?: ('Pol. ' . $parcela->poligono . ' - Par. ' . $parcela->parcela),
            ] : null,
            'hanegadas' => $hanegadasTotales,
            'periodo' => ['inicio' => $inicio->toDateString(), 'fin' => $fin->toDateString()],
            'metodos' => $metodos,
        ]);
    }

    /**
     * Única fuente de verdad para "ingresos", "gastos" y "ganancia neta" en
     * toda la pestaña de Análisis: ganancia neta = ingresos - gastos, con
     * margen (%) = ganancia neta / gastos * 100. "Sin datos" (no se calcula
     * el margen, para no dividir por cero) si no hay recolección registrada
     * en el periodo/agrupación, o si no hay ningún gasto.
     */
    private function resumenFinanciero(bool $hayIngresos, float $ingresos, float $costes): array
    {
        $hayGastos = $costes > 0;
        $sinDatos = !$hayIngresos || !$hayGastos;
        $gananciaNeta = $ingresos - $costes;

        return [
            'ingresos' => round($ingresos, 2),
            'costes' => round($costes, 2),
            'gananciaNeta' => round($gananciaNeta, 2),
            'margen' => $sinDatos ? null : round($gananciaNeta / $costes * 100, 2),
            'sinDatos' => $sinDatos,
        ];
    }

    /**
     * Ganancia neta por parcela: ingresos de recolección menos fumigaciones,
     * operaciones, riego e impuestos. Si se filtra por parcela devuelve solo
     * esa fila; si no, todas las parcelas del admin para poder compararlas.
     */
    public function rentabilidad(Request $request)
    {
        [$inicio, $fin] = $this->rangoFechas($request);
        $parcelaId = $request->query('parcela_id');

        $parcelas = $parcelaId ? collect([Parcela::findOrFail($parcelaId)]) : Parcela::all();
        $ids = $parcelas->pluck('id');

        $ingresosPorParcela = Recoleccion::whereIn('parcela_id', $ids)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('parcela_id, SUM(kilos * precio_medio_kg) as total')
            ->groupBy('parcela_id')
            ->pluck('total', 'parcela_id');

        $costeOperacionesPorParcela = Operacion::whereIn('parcela_id', $ids)
            ->whereBetween('hora_inicio', [$inicio, $fin])
            ->selectRaw('parcela_id, SUM(precio) as total')
            ->groupBy('parcela_id')
            ->pluck('total', 'parcela_id');

        // gastos_riego solo guarda año/mes, no una fecha: se filtra por los
        // años que cubre el rango seleccionado
        $anios = range((int) $inicio->format('Y'), (int) $fin->format('Y'));
        $costeRiegoPorParcela = GastoRiego::whereIn('parcela_id', $ids)
            ->whereIn('anio', $anios)
            ->selectRaw('parcela_id, SUM(importe) as total')
            ->groupBy('parcela_id')
            ->pluck('total', 'parcela_id');

        // reparto por hanegadas de fumigaciones en lote: igual criterio que
        // en costesPorMetodo, se cargan todas las del periodo y se enriquecen
        $fumigaciones = Fumigacion::with(['parcela', 'productos'])
            ->whereBetween('hora_inicio', [$inicio, $fin])
            ->get();
        $fumigaciones = $this->coste->enriquecerHanegadas($fumigaciones);

        $costeFumigacionPorParcela = [];
        foreach ($fumigaciones as $fum) {
            $costeFumigacionPorParcela[$fum->parcela_id] = ($costeFumigacionPorParcela[$fum->parcela_id] ?? 0)
                + $this->coste->costeOperacionParcela($fum)
                + $this->coste->costeMaterialParcela($fum);
        }

        $resultado = $parcelas->map(function ($p) use (
            $ingresosPorParcela,
            $costeOperacionesPorParcela,
            $costeRiegoPorParcela,
            $costeFumigacionPorParcela
        ) {
            $ingresos = (float) $ingresosPorParcela->get($p->id, 0);
            $costes = (float) $costeOperacionesPorParcela->get($p->id, 0)
                + (float) $costeRiegoPorParcela->get($p->id, 0)
                + (float) ($costeFumigacionPorParcela[$p->id] ?? 0)
                + (float) ($p->impuesto_municipal ?? 0)
                + (float) ($p->impuesto_cequiaje ?? 0);

            $financiero = $this->resumenFinanciero($ingresosPorParcela->has($p->id), $ingresos, $costes);
            $hanegadas = (float) $p->dimension_hanegadas;

            return array_merge([
                'parcela_id' => $p->id,
                'nombre' => $p->nombre ?: ('Pol. ' . $p->poligono . ' - Par. ' . $p->parcela),
                'hanegadas' => $hanegadas,
            ], $financiero, [
                'gananciaNetaHanegada' => $hanegadas > 0 ? round($financiero['gananciaNeta'] / $hanegadas, 2) : null,
            ]);
        })->values();

        return response()->json([
            'periodo' => ['inicio' => $inicio->toDateString(), 'fin' => $fin->toDateString()],
            'parcelas' => $resultado,
        ]);
    }

    /**
     * Ganancia neta histórica: para cada año con algún dato (recolección,
     * operaciones, fumigaciones o riego), la ganancia neta TOTAL sumando
     * todas las parcelas del admin. Pensado para ver la evolución año a año.
     */
    public function rentabilidadPorAnio(Request $request)
    {
        $ingresosPorAnio = Recoleccion::selectRaw('YEAR(fecha) as anio, SUM(kilos * precio_medio_kg) as total')
            ->groupBy('anio')
            ->pluck('total', 'anio');

        $costeOperacionesPorAnio = Operacion::selectRaw('YEAR(hora_inicio) as anio, SUM(precio) as total')
            ->groupBy('anio')
            ->pluck('total', 'anio');

        $costeRiegoPorAnio = GastoRiego::selectRaw('anio, SUM(importe) as total')
            ->groupBy('anio')
            ->pluck('total', 'anio');

        // igual que en costesPorMetodo/rentabilidad: se cargan TODAS las
        // fumigaciones (sin filtrar por fecha) para que el reparto por
        // hanegadas de un lote de tractor conozca a todas sus hermanas
        $fumigaciones = Fumigacion::with(['parcela', 'productos'])->get();
        $fumigaciones = $this->coste->enriquecerHanegadas($fumigaciones);

        $costeFumigacionPorAnio = [];
        foreach ($fumigaciones as $fum) {
            $anio = (int) substr((string) $fum->hora_inicio, 0, 4);
            $costeFumigacionPorAnio[$anio] = ($costeFumigacionPorAnio[$anio] ?? 0)
                + $this->coste->costeOperacionParcela($fum)
                + $this->coste->costeMaterialParcela($fum);
        }

        // el impuesto es un coste anual fijo por parcela: se suma una vez por
        // cada año que aparezca en el histórico, igual que en rentabilidad()
        // suma el impuesto completo del año seleccionado
        $impuestoAnualTotal = Parcela::all()->sum(
            fn($p) => (float) ($p->impuesto_municipal ?? 0) + (float) ($p->impuesto_cequiaje ?? 0)
        );

        $anios = $ingresosPorAnio->keys()
            ->merge($costeOperacionesPorAnio->keys())
            ->merge($costeRiegoPorAnio->keys())
            ->merge(array_keys($costeFumigacionPorAnio))
            ->unique()
            ->sort()
            ->values();

        $resultado = $anios->map(function ($anio) use (
            $ingresosPorAnio,
            $costeOperacionesPorAnio,
            $costeRiegoPorAnio,
            $costeFumigacionPorAnio,
            $impuestoAnualTotal
        ) {
            $ingresos = (float) $ingresosPorAnio->get($anio, 0);
            $costes = (float) $costeOperacionesPorAnio->get($anio, 0)
                + (float) $costeRiegoPorAnio->get($anio, 0)
                + (float) ($costeFumigacionPorAnio[$anio] ?? 0)
                + $impuestoAnualTotal;

            $financiero = $this->resumenFinanciero($ingresosPorAnio->has($anio), $ingresos, $costes);

            return array_merge(['anio' => (int) $anio], $financiero);
        })->values();

        return response()->json(['anios' => $resultado]);
    }

    public function resumenParcela(Request $request)
    {
        // el scope global ya filtra por admin: si la parcela no es suya, 404
        $parcela = Parcela::findOrFail($request->query('parcela_id'));

        $anio = (string) $request->query('anio', now()->year);
        $tipo = $request->query('tipo', 'todas');

        $operaciones = Operacion::with('parcela')
            ->get()
            ->filter(fn($o) => str_starts_with((string) $o->hora_inicio, $anio))
            ->values();

        $fumigaciones = Fumigacion::with(['parcela', 'productos'])
            ->get()
            ->filter(fn($f) => str_starts_with((string) $f->hora_inicio, $anio))
            ->values();
        $fumigaciones = $this->coste->enriquecerHanegadas($fumigaciones);

        $datosParcela = $this->costeParcelaTipo($parcela, $tipo, $operaciones, $fumigaciones);

        $hanegadas = (float) $parcela->dimension_hanegadas;
        $gastoPorHanegada = $hanegadas > 0 ? $datosParcela['costeTotal'] / $hanegadas : 0.0;

        // media del resto de parcelas del mismo admin (el scope global ya las filtra)
        $mediaResto = $this->mediaGastoPorHanegadaResto($parcela, $tipo, $operaciones, $fumigaciones);

        return response()->json([
            'parcela' => [
                'id' => $parcela->id,
                'nombre' => $parcela->nombre ?: ('Pol. ' . $parcela->poligono . ' - Par. ' . $parcela->parcela),
                'hanegadas' => $hanegadas,
            ],
            'anio' => $anio,
            'tipo' => $tipo,
            'gastoTotal' => round($datosParcela['costeTotal'], 2),
            'gastoPorHanegada' => round($gastoPorHanegada, 2),
            'fumigacion' => $datosParcela['fumigacion'],
            'comparativa' => [
                'gastoPorHanegadaParcela' => round($gastoPorHanegada, 2),
                'gastoPorHanegadaMediaResto' => round($mediaResto, 2),
            ],
        ]);
    }

    /**
     * Calcula el coste de una parcela para el tipo seleccionado, reutilizando
     * el reparto proporcional por hanegadas ya existente en CosteFumigacionService.
     */
    private function costeParcelaTipo(Parcela $parcela, string $tipo, Collection $operaciones, Collection $fumigaciones): array
    {
        $opsParcela = $operaciones->filter(fn($o) => $o->parcela_id === $parcela->id);
        $fumsParcela = $fumigaciones->filter(fn($f) => $f->parcela_id === $parcela->id);

        $costeFumigacion = $this->costeFumigacionParcela($fumsParcela);
        $hayFumigaciones = $fumsParcela->isNotEmpty();

        if ($tipo === 'fumigacion') {
            return [
                'costeTotal' => $costeFumigacion['costeTotal'],
                'fumigacion' => $hayFumigaciones ? $costeFumigacion : null,
            ];
        }

        if ($tipo === 'todas') {
            $costeOperaciones = $opsParcela->sum(fn($o) => (float) ($o->precio ?? 0));
            return [
                'costeTotal' => $costeOperaciones + $costeFumigacion['costeTotal'],
                'fumigacion' => $hayFumigaciones ? $costeFumigacion : null,
            ];
        }

        // tipo_operacion concreto (poda, riego, abonado, mantenimiento, tractor)
        $costeTipo = $opsParcela
            ->filter(fn($o) => $o->tipo_operacion === $tipo)
            ->sum(fn($o) => (float) ($o->precio ?? 0));

        return [
            'costeTotal' => $costeTipo,
            'fumigacion' => null,
        ];
    }

    /**
     * Desglose € producto vs € mano de obra y litros totales aplicados en la parcela.
     */
    private function costeFumigacionParcela(Collection $fumsParcela): array
    {
        $costeProducto = 0.0;
        $costeManoObra = 0.0;
        $litros = 0.0;

        foreach ($fumsParcela as $fum) {
            $costeManoObra += $fum->metodo_aplicacion === 'tractor'
                ? $this->coste->costeTractorParcela($fum)
                : $this->coste->costeMochilaParcela($fum);
            $costeProducto += $this->coste->costeMaterialParcela($fum);
            $litros += $this->coste->calcularLitros($fum);
        }

        return [
            'costeProducto' => round($costeProducto, 2),
            'costeManoObra' => round($costeManoObra, 2),
            'litros' => round($litros, 0),
            'costeTotal' => $costeProducto + $costeManoObra,
        ];
    }

    private function mediaGastoPorHanegadaResto(Parcela $parcelaSeleccionada, string $tipo, Collection $operaciones, Collection $fumigaciones): float
    {
        $resto = Parcela::where('id', '!=', $parcelaSeleccionada->id)->get();

        $ratios = $resto
            ->filter(fn($p) => (float) $p->dimension_hanegadas > 0)
            ->map(function ($p) use ($tipo, $operaciones, $fumigaciones) {
                $datos = $this->costeParcelaTipo($p, $tipo, $operaciones, $fumigaciones);
                return $datos['costeTotal'] / (float) $p->dimension_hanegadas;
            });

        return $ratios->isEmpty() ? 0.0 : $ratios->avg();
    }
}
