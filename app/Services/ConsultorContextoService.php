<?php

namespace App\Services;

use App\Models\Explotacion;
use App\Models\Fumigacion;
use App\Models\GastoRiego;
use App\Models\Operacion;
use App\Models\Parcela;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Recoleccion;
use App\Models\RiegoManta;
use Illuminate\Support\Carbon;

class ConsultorContextoService
{
    // La campaña va del 1 de octubre al 30 de septiembre del año siguiente.
    // Es el ciclo natural del cultivo en la Ribera Alta (caqui y cítricos).
    public const INICIO_CAMPANIA_MES = 10; // octubre
    public const INICIO_CAMPANIA_DIA = 1;
    public const FIN_CAMPANIA_MES    = 9;  // septiembre
    public const FIN_CAMPANIA_DIA    = 30;

    // Si el contexto supera este tamaño se comprime a solo TOTALES y
    // el detalle línea a línea del módulo que mencione la pregunta.
    public const LIMITE_CARACTERES = 40000;

    // Palabras clave → módulo. El consultor detecta qué módulo ampliar
    // cuando el contexto completo no cabe.
    private const MAPA_MODULOS = [
        'gastos'          => ['gasto', 'coste', 'factura', 'pagado', 'pago', 'precio', 'importe', 'operacion', 'operación'],
        'fumigaciones'    => ['fumig', 'caldo', 'tratamiento', 'plaga', 'fitosanitario', 'mochila', 'tractor', 'herbicida', 'fungicida', 'insecticida'],
        'fertilizaciones' => ['abono', 'fertilizante', 'fertilizacion', 'fertilización', 'abonar', 'nitrógeno', 'nitrogeno'],
        'almacen'         => ['stock', 'almacén', 'almacen', 'producto', 'existencias', 'inventario'],
        'recolecciones'   => ['cosecha', 'kg', 'kilo', 'recolec', 'produccion', 'producción', 'fruta', 'ingreso', 'venta'],
        'parcelas'        => ['parcela', 'superficie', 'hanegada', 'hectárea', 'hectarea', 'poligono', 'polígono'],
        'riego'           => ['riego', 'agua', 'goteo', 'manta', 'regar'],
    ];

    public function __construct(
        private CosteFumigacionService $costeFumigacion,
        private RiegoService $riegoService
    ) {}

    // Devuelve [inicio => Carbon, fin => Carbon] de la campaña actual.
    public function campanaActual(): array
    {
        $hoy = Carbon::today();
        // Si estamos a partir del 1 de octubre, la campaña empieza este año
        if ($hoy->month >= self::INICIO_CAMPANIA_MES) {
            $inicio = Carbon::create($hoy->year, self::INICIO_CAMPANIA_MES, self::INICIO_CAMPANIA_DIA)->startOfDay();
            $fin    = Carbon::create($hoy->year + 1, self::FIN_CAMPANIA_MES, self::FIN_CAMPANIA_DIA)->endOfDay();
        } else {
            $inicio = Carbon::create($hoy->year - 1, self::INICIO_CAMPANIA_MES, self::INICIO_CAMPANIA_DIA)->startOfDay();
            $fin    = Carbon::create($hoy->year, self::FIN_CAMPANIA_MES, self::FIN_CAMPANIA_DIA)->endOfDay();
        }
        return ['inicio' => $inicio, 'fin' => $fin];
    }

    // Etiqueta legible de la campaña, p. ej. "2025/2026".
    public function etiquetaCampana(Carbon $inicio, Carbon $fin): string
    {
        return $inicio->year . '/' . $fin->year;
    }

    // Construye el bloque de contexto completo para una campaña.
    // $pregunta se usa para decidir qué módulo ampliar si hay que comprimir.
    public function construir(string $pregunta, ?Carbon $inicio = null, ?Carbon $fin = null): string
    {
        if ($inicio === null || $fin === null) {
            ['inicio' => $inicio, 'fin' => $fin] = $this->campanaActual();
        }

        // Carga de datos con el scope de admin ya aplicado por los modelos
        $parcelas   = Parcela::with(['explotacion:id,nombre', 'propietario:id,nombre'])->get();
        $parcelaIds = $parcelas->pluck('id');

        $operaciones = Operacion::with(['parcela:id,nombre,poligono,parcela', 'producto:id,nombre,unidad'])
            ->whereBetween('hora_inicio', [$inicio, $fin])
            ->get();

        $todasFumigaciones = Fumigacion::with(['parcela', 'productos'])
            ->whereBetween('hora_inicio', [$inicio, $fin])
            ->get();
        $todasFumigaciones = $this->costeFumigacion->enriquecerHanegadas($todasFumigaciones);

        $recolecciones = Recoleccion::with('parcela:id,nombre,poligono,parcela')
            ->whereBetween('fecha', [$inicio, $fin])
            ->get();

        $productos = Producto::all();

        $costesRiego = $this->riegoService->costeTotalPorParcela(
            $parcelaIds,
            [$inicio->year, $fin->year],
            $inicio->toDateString(),
            $fin->toDateString()
        );

        // ---- TOTALES POR PARCELA ----
        $totalesPorParcela = $this->calcularTotalesPorParcela(
            $parcelas, $operaciones, $todasFumigaciones, $recolecciones, $costesRiego
        );

        $bloquesTotales = $this->formatearTotales($totalesPorParcela, $this->etiquetaCampana($inicio, $fin));

        // ---- DETALLE POR MÓDULO ----
        $bloqueDetalle = [
            'gastos'          => $this->bloqueGastos($parcelas, $operaciones, $costesRiego, $todasFumigaciones),
            'fumigaciones'    => $this->bloqueFumigaciones($parcelas, $todasFumigaciones),
            'fertilizaciones' => $this->bloqueFertilizaciones($parcelas, $operaciones),
            'almacen'         => $this->bloqueAlmacen($productos),
            'recolecciones'   => $this->bloqueRecolecciones($parcelas, $recolecciones),
            'parcelas'        => $this->bloqueParcelas($parcelas),
            'riego'           => $this->bloqueRiego($parcelas, $inicio, $fin),
        ];

        $contextoCompleto = $bloquesTotales . "\n\n" . implode("\n\n", $bloqueDetalle);

        // Si cabe, devolvemos todo
        if (mb_strlen($contextoCompleto) <= self::LIMITE_CARACTERES) {
            return $contextoCompleto;
        }

        // Si no cabe, solo TOTALES + el detalle del módulo que menciona la pregunta
        $moduloDetectado = $this->detectarModulo($pregunta);
        $contextoCortado = $bloquesTotales;
        if ($moduloDetectado && isset($bloqueDetalle[$moduloDetectado])) {
            $contextoCortado .= "\n\n" . $bloqueDetalle[$moduloDetectado];
        } else {
            // sin coincidencia: añadimos parcelas y almacén que son los más cortos
            $contextoCortado .= "\n\n" . $bloqueDetalle['parcelas'] . "\n\n" . $bloqueDetalle['almacen'];
        }
        $contextoCortado .= "\n\n[Contexto resumido por volumen de datos. El detalle completo de otros módulos no se incluye en esta consulta.]";

        return $contextoCortado;
    }

    // Detecta qué módulo menciona la pregunta usando el mapa de palabras clave.
    private function detectarModulo(string $pregunta): ?string
    {
        $preguntaNorm = mb_strtolower($pregunta);
        foreach (self::MAPA_MODULOS as $modulo => $palabras) {
            foreach ($palabras as $palabra) {
                if (str_contains($preguntaNorm, $palabra)) {
                    return $modulo;
                }
            }
        }
        return null;
    }

    // Calcula gasto total, kg e ingresos por parcela para el bloque TOTALES.
    private function calcularTotalesPorParcela($parcelas, $operaciones, $fumigaciones, $recolecciones, $costesRiego): array
    {
        $totales = [];

        foreach ($parcelas as $p) {
            $gastoOps = $operaciones
                ->where('parcela_id', $p->id)
                ->sum(fn($o) => (float) ($o->precio ?? 0));

            $gastoFum = $fumigaciones
                ->where('parcela_id', $p->id)
                ->sum(fn($f) =>
                    $this->costeFumigacion->costeOperacionParcela($f)
                    + $this->costeFumigacion->costeMaterialParcela($f)
                );

            $gastoRiego  = (float) $costesRiego->get($p->id, 0);
            $gastoImptos = (float) ($p->impuesto_municipal ?? 0) + (float) ($p->impuesto_cequiaje ?? 0);
            $gastoTotal  = round($gastoOps + $gastoFum + $gastoRiego + $gastoImptos, 2);

            $recoParcela  = $recolecciones->where('parcela_id', $p->id);
            $kgTotal      = round((float) $recoParcela->sum('kilos'), 2);
            $ingresoTotal = round((float) $recoParcela->sum(fn($r) => (float) $r->kilos * (float) $r->precio_medio_kg), 2);
            $ganancia     = round($ingresoTotal - $gastoTotal, 2);

            $totales[$p->id] = [
                'nombre'        => $p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}",
                'explotacion'   => $p->explotacion->nombre ?? 'Sin explotación',
                'hanegadas'     => (float) ($p->dimension_hanegadas ?? 0),
                'gasto_total'   => $gastoTotal,
                'kg_total'      => $kgTotal,
                'ingreso_total' => $ingresoTotal,
                'ganancia'      => $ganancia,
            ];
        }

        return $totales;
    }

    private function formatearTotales(array $totales, string $etiquetaCampana): string
    {
        $lineas = ["TOTALES CAMPAÑA {$etiquetaCampana}:"];
        foreach ($totales as $t) {
            $lineas[] = "  {$t['nombre']} ({$t['explotacion']}, {$t['hanegadas']} hanegadas)"
                . " | gasto total: " . number_format($t['gasto_total'], 2, ',', '.') . " €"
                . " | kg recolectados: {$t['kg_total']}"
                . " | ingresos: " . number_format($t['ingreso_total'], 2, ',', '.') . " €"
                . " | ganancia neta: " . number_format($t['ganancia'], 2, ',', '.') . " €";
        }

        // Sumas globales
        $gastoGlobal    = array_sum(array_column($totales, 'gasto_total'));
        $kgGlobal       = array_sum(array_column($totales, 'kg_total'));
        $ingresoGlobal  = array_sum(array_column($totales, 'ingreso_total'));
        $gananciaGlobal = array_sum(array_column($totales, 'ganancia'));

        $lineas[] = "  TOTAL EXPLOTACIONES"
            . " | gasto total: " . number_format($gastoGlobal, 2, ',', '.') . " €"
            . " | kg recolectados: {$kgGlobal}"
            . " | ingresos: " . number_format($ingresoGlobal, 2, ',', '.') . " €"
            . " | ganancia neta: " . number_format($gananciaGlobal, 2, ',', '.') . " €";

        return implode("\n", $lineas);
    }

    private function bloqueGastos($parcelas, $operaciones, $costesRiego, $fumigaciones): string
    {
        $lineas = ["GASTOS POR PARCELA (detalle por tipo):"];
        foreach ($parcelas as $p) {
            $nombre = $p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}";
            $opsParcela = $operaciones->where('parcela_id', $p->id);

            foreach ($opsParcela->groupBy('tipo_operacion') as $tipo => $ops) {
                $total = round((float) $ops->sum('precio'), 2);
                $lineas[] = "  {$nombre} | {$tipo}: " . number_format($total, 2, ',', '.') . " €";
            }

            $fumParcela = $fumigaciones->where('parcela_id', $p->id);
            if ($fumParcela->isNotEmpty()) {
                $totalFum = round($fumParcela->sum(fn($f) =>
                    $this->costeFumigacion->costeOperacionParcela($f)
                    + $this->costeFumigacion->costeMaterialParcela($f)
                ), 2);
                $lineas[] = "  {$nombre} | fumigaciones: " . number_format($totalFum, 2, ',', '.') . " €";
            }

            $riego = (float) $costesRiego->get($p->id, 0);
            if ($riego > 0) {
                $lineas[] = "  {$nombre} | riego: " . number_format($riego, 2, ',', '.') . " €";
            }

            $imptos = round((float) ($p->impuesto_municipal ?? 0) + (float) ($p->impuesto_cequiaje ?? 0), 2);
            if ($imptos > 0) {
                $lineas[] = "  {$nombre} | impuestos: " . number_format($imptos, 2, ',', '.') . " €";
            }
        }
        return implode("\n", $lineas);
    }

    private function bloqueFumigaciones($parcelas, $fumigaciones): string
    {
        $lineas = ["FUMIGACIONES (tratamientos):"];
        foreach ($fumigaciones as $f) {
            $p = $parcelas->firstWhere('id', $f->parcela_id);
            $nombreParcela = $p ? ($p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}") : "parcela#{$f->parcela_id}";
            $explotacion   = $p ? ($p->explotacion->nombre ?? '') : '';
            $fecha   = $f->hora_inicio ? Carbon::parse($f->hora_inicio)->format('d/m/Y') : 'sin fecha';
            $metodo  = $f->metodo_aplicacion ?? '';
            $litros  = round($this->costeFumigacion->calcularLitros($f), 0);
            $costeOp = round($this->costeFumigacion->costeOperacionParcela($f), 2);
            $costeMat = round($this->costeFumigacion->costeMaterialParcela($f), 2);
            $productos = $f->productos->map(fn($prod) => $prod->nombre)->implode(', ');

            $lineas[] = "  {$fecha} | {$nombreParcela} ({$explotacion}) | {$metodo}"
                . " | {$litros} litros caldo"
                . " | coste operación: " . number_format($costeOp, 2, ',', '.') . " €"
                . " | coste material: " . number_format($costeMat, 2, ',', '.') . " €"
                . ($productos ? " | productos: {$productos}" : '');
        }
        if (count($lineas) === 1) {
            $lineas[] = "  Sin fumigaciones en este periodo.";
        }
        return implode("\n", $lineas);
    }

    private function bloqueFertilizaciones($parcelas, $operaciones): string
    {
        $lineas = ["FERTILIZACIONES (abonados):"];
        $abonados = $operaciones->where('tipo_operacion', 'abonado');
        foreach ($abonados as $op) {
            $p = $parcelas->firstWhere('id', $op->parcela_id);
            $nombreParcela = $p ? ($p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}") : "parcela#{$op->parcela_id}";
            $explotacion   = $p ? ($p->explotacion->nombre ?? '') : '';
            $fecha    = $op->hora_inicio ? Carbon::parse($op->hora_inicio)->format('d/m/Y') : 'sin fecha';
            $producto = $op->producto ? "{$op->producto->nombre} ({$op->dosis} {$op->producto->unidad})" : 'producto no especificado';
            $precio   = round((float) ($op->precio ?? 0), 2);

            $lineas[] = "  {$fecha} | {$nombreParcela} ({$explotacion})"
                . " | {$producto}"
                . " | coste: " . number_format($precio, 2, ',', '.') . " €";
        }
        if (count($lineas) === 1) {
            $lineas[] = "  Sin abonados registrados en este periodo.";
        }
        return implode("\n", $lineas);
    }

    private function bloqueAlmacen($productos): string
    {
        $lineas = ["ALMACEN (stock actual de productos):"];
        foreach ($productos as $prod) {
            $bajo = $prod->stock_actual <= $prod->stock_minimo ? ' [STOCK BAJO]' : '';
            $lineas[] = "  {$prod->nombre} | stock: {$prod->stock_actual} {$prod->unidad}"
                . " | mínimo: {$prod->stock_minimo} {$prod->unidad}"
                . " | precio unitario: " . number_format((float) $prod->precio, 2, ',', '.') . " €/{$prod->unidad}"
                . ($prod->ubicacion ? " | ubicación: {$prod->ubicacion}" : '')
                . $bajo;
        }
        if (count($lineas) === 1) {
            $lineas[] = "  Sin productos en el almacén.";
        }
        return implode("\n", $lineas);
    }

    private function bloqueRecolecciones($parcelas, $recolecciones): string
    {
        $lineas = ["RECOLECCIONES:"];
        foreach ($recolecciones as $r) {
            $p = $parcelas->firstWhere('id', $r->parcela_id);
            $nombreParcela = $p ? ($p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}") : "parcela#{$r->parcela_id}";
            $explotacion   = $p ? ($p->explotacion->nombre ?? '') : '';
            $fecha   = Carbon::parse($r->fecha)->format('d/m/Y');
            $ingreso = round((float) $r->kilos * (float) $r->precio_medio_kg, 2);

            $lineas[] = "  {$fecha} | {$nombreParcela} ({$explotacion})"
                . " | variedad: {$r->variedad}"
                . " | {$r->kilos} kg"
                . " | precio medio: " . number_format((float) $r->precio_medio_kg, 2, ',', '.') . " €/kg"
                . " | ingreso: " . number_format($ingreso, 2, ',', '.') . " €";
        }
        if (count($lineas) === 1) {
            $lineas[] = "  Sin recolecciones registradas en este periodo.";
        }
        return implode("\n", $lineas);
    }

    private function bloqueParcelas($parcelas): string
    {
        $lineas = ["PARCELAS:"];
        foreach ($parcelas as $p) {
            $nombre      = $p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}";
            $propietario = $p->propietario->nombre ?? 'sin propietario';
            $lineas[] = "  {$nombre} | explotación: " . ($p->explotacion->nombre ?? 'sin explotación')
                . " | propietario: {$propietario}"
                . " | variedad: {$p->variedad}"
                . " | {$p->dimension_hanegadas} hanegadas"
                . " | {$p->num_arboles} árboles"
                . " | pol.{$p->poligono} par.{$p->parcela}"
                . " | impuesto municipal: " . number_format((float) ($p->impuesto_municipal ?? 0), 2, ',', '.') . " €"
                . " | impuesto cequiaje: " . number_format((float) ($p->impuesto_cequiaje ?? 0), 2, ',', '.') . " €";
        }
        if (count($lineas) === 1) {
            $lineas[] = "  Sin parcelas registradas.";
        }
        return implode("\n", $lineas);
    }

    private function bloqueRiego($parcelas, Carbon $inicio, Carbon $fin): string
    {
        $gastosRiego = GastoRiego::whereIn('parcela_id', $parcelas->pluck('id'))
            ->whereIn('anio', [$inicio->year, $fin->year])
            ->get();
        $riegosManta = RiegoManta::whereIn('parcela_id', $parcelas->pluck('id'))
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->get();

        $lineas = ["RIEGO:"];
        foreach ($parcelas as $p) {
            $nombre      = $p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}";
            $explotacion = $p->explotacion->nombre ?? '';

            foreach ($gastosRiego->where('parcela_id', $p->id) as $g) {
                $lineas[] = "  {$nombre} ({$explotacion}) | {$g->concepto}"
                    . " | año {$g->anio} mes {$g->mes}"
                    . " | importe: " . number_format((float) $g->importe, 2, ',', '.') . " €";
            }

            foreach ($riegosManta->where('parcela_id', $p->id) as $r) {
                $fecha = Carbon::parse($r->fecha)->format('d/m/Y');
                $lineas[] = "  {$nombre} ({$explotacion}) | riego manta | {$fecha}"
                    . " | {$r->hanegadas} hanegadas"
                    . " | importe: " . number_format((float) $r->importe, 2, ',', '.') . " €";
            }
        }
        if (count($lineas) === 1) {
            $lineas[] = "  Sin registros de riego en este periodo.";
        }
        return implode("\n", $lineas);
    }
}
