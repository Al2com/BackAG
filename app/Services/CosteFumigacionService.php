<?php

namespace App\Services;

use App\Models\Fumigacion;
use Illuminate\Support\Collection;

class CosteFumigacionService
{
    private const LITROS_TRACTOR = 1500;
    private const LITROS_MOCHILA = 12;

    /**
     * Enriquece cada fumigación con las hanegadas del tratamiento completo
     * (todas las "hermanas": mismo hora_inicio + metodo_aplicacion + turbos),
     * necesarias para repartir el coste del tractor proporcionalmente.
     */
    public function enriquecerHanegadas(Collection $fumigaciones): Collection
    {
        $fumigaciones->each(function ($fum) use ($fumigaciones) {
            $hermanas = $fumigaciones->filter(fn($f) =>
                $f->hora_inicio === $fum->hora_inicio &&
                $f->metodo_aplicacion === $fum->metodo_aplicacion &&
                $f->turbos === $fum->turbos
            );
            $fum->total_hanegadas = $hermanas->sum(fn($f) => (float) ($f->parcela->dimension_hanegadas ?? 0));
            $fum->hanegadas_parcela = (float) ($fum->parcela->dimension_hanegadas ?? 0);
        });

        return $fumigaciones;
    }

    /**
     * Proporción de una fumigación que corresponde a la parcela: por hanegadas
     * si es tractor (reparto entre el lote), o a partes iguales si es mochila.
     */
    public function calcularProporcion(Fumigacion $fum): float
    {
        if ($fum->metodo_aplicacion === 'tractor' && $fum->total_hanegadas > 0) {
            return $fum->hanegadas_parcela / $fum->total_hanegadas;
        }
        return 1 / ($fum->num_parcelas ?: 1);
    }

    /**
     * Litros aplicados en la parcela para esta fumigación, repartidos igual que el coste.
     */
    public function calcularLitros(Fumigacion $fum): float
    {
        $unidades = $fum->metodo_aplicacion === 'tractor' ? $fum->turbos : $fum->mochilas;
        $litrosPorUnidad = $fum->metodo_aplicacion === 'tractor' ? self::LITROS_TRACTOR : self::LITROS_MOCHILA;
        $litrosTotales = $unidades * $litrosPorUnidad;

        if ($fum->metodo_aplicacion === 'tractor' && $fum->total_hanegadas > 0) {
            return $litrosTotales * ($fum->hanegadas_parcela / $fum->total_hanegadas);
        }
        return $litrosTotales / ($fum->num_parcelas ?: 1);
    }

    /**
     * Coste del tractor imputado a una parcela.
     * Reparte (turbos × precio_turbo) entre las hanegadas del tratamiento.
     */
    public function costeTractorParcela(Fumigacion $fum): float
    {
        if ($fum->total_hanegadas <= 0) {
            return 0.0;
        }

        return (float) $fum->turbos
            * (float) $fum->precio_turbo
            * ($fum->hanegadas_parcela / $fum->total_hanegadas);
    }

    /**
     * Coste de la mochila imputado a la parcela: ya viene guardado como precio
     * total del tratamiento (sin reparto adicional por hanegadas).
     */
    public function costeMochilaParcela(Fumigacion $fum): float
    {
        return (float) $fum->precio;
    }

    /**
     * Coste de material (productos) aplicado a la parcela en una fumigación,
     * repartido con la misma proporción que el coste de tratamiento.
     */
    public function costeMaterialParcela(Fumigacion $fum): float
    {
        $unidades = $fum->metodo_aplicacion === 'tractor' ? $fum->turbos : $fum->mochilas;
        $proporcion = $this->calcularProporcion($fum);

        $costeMaterial = 0.0;
        foreach ($fum->productos as $prod) {
            $cantidad = $prod->pivot->dosis_introducida * $unidades * $proporcion;
            $precioUnidad = (float) ($prod->pivot->precio ?? $prod->precio ?? 0);
            $costeMaterial += $cantidad * $precioUnidad;
        }

        return $costeMaterial;
    }
}
