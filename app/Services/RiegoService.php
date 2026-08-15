<?php

namespace App\Services;

use App\Models\GastoRiego;
use App\Models\RiegoManta;
use Illuminate\Support\Collection;

/**
 * Única fuente de verdad para el gasto de riego, consumida por GastosController
 * (detalle de lectura) y AnalisisController (rentabilidad/ganancia neta).
 *
 * El riego vive en dos tablas:
 * - gastos_riego: recibo mensual (agua de goteo, abono, y mantenimiento de
 *   CUALQUIER parcela, manta o goteo), agrupado por año/mes.
 * - riegos_manta: evento de riego a manta por día, con importe calculado
 *   desde las hanegadas de la parcela en ese momento.
 */
class RiegoService
{
    /**
     * Coste total de riego (gastos_riego + riegos_manta) por parcela.
     * gastos_riego se filtra por año (es la granularidad que guarda);
     * riegos_manta se filtra por fecha exacta si se pasa un rango.
     */
    public function costeTotalPorParcela(
        iterable $parcelaIds,
        array $anios,
        ?string $fechaInicioManta = null,
        ?string $fechaFinManta = null
    ): Collection {
        $parcelaIds = collect($parcelaIds)->values();

        $gastosRiegoPorParcela = GastoRiego::whereIn('parcela_id', $parcelaIds)
            ->whereIn('anio', $anios)
            ->selectRaw('parcela_id, SUM(importe) as total')
            ->groupBy('parcela_id')
            ->pluck('total', 'parcela_id');

        $riegoMantaQuery = RiegoManta::whereIn('parcela_id', $parcelaIds);
        if ($fechaInicioManta && $fechaFinManta) {
            $riegoMantaQuery->whereBetween('fecha', [$fechaInicioManta, $fechaFinManta]);
        }
        $riegoMantaPorParcela = $riegoMantaQuery
            ->selectRaw('parcela_id, SUM(importe) as total')
            ->groupBy('parcela_id')
            ->pluck('total', 'parcela_id');

        return $parcelaIds->mapWithKeys(fn($id) => [
            $id => (float) $gastosRiegoPorParcela->get($id, 0) + (float) $riegoMantaPorParcela->get($id, 0),
        ]);
    }

    /**
     * Coste total de riego por año, sumando TODAS las parcelas del admin.
     * Para el histórico de Análisis.
     */
    public function costeTotalPorAnio(): Collection
    {
        $gastosRiegoPorAnio = GastoRiego::selectRaw('anio, SUM(importe) as total')
            ->groupBy('anio')
            ->pluck('total', 'anio');

        $riegoMantaPorAnio = RiegoManta::selectRaw('YEAR(fecha) as anio, SUM(importe) as total')
            ->groupBy('anio')
            ->pluck('total', 'anio');

        return $gastosRiegoPorAnio->keys()
            ->merge($riegoMantaPorAnio->keys())
            ->unique()
            ->mapWithKeys(fn($anio) => [
                $anio => (float) $gastosRiegoPorAnio->get($anio, 0) + (float) $riegoMantaPorAnio->get($anio, 0),
            ]);
    }
}
