<?php

namespace App\Services;

use App\Models\Fumigacion;
use Illuminate\Support\Collection;

class CosteFumigacionService
{
    // litros por turbo/tanque de tractor: fijo, no varía entre fumigaciones
    public const LITROS_TRACTOR = 1500;

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
        // en mochila la capacidad depende del tipo (estándar 16L, pilas 5L, u otra a mano)
        // y se guarda por fila en litros_agua; el tractor siempre usa la misma capacidad fija
        $litrosPorUnidad = $fum->metodo_aplicacion === 'tractor' ? self::LITROS_TRACTOR : (float) ($fum->litros_agua ?? 0);
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

    /**
     * Coste de mano de obra + tratamiento imputado a la parcela, sin material:
     * despacha a costeTractorParcela o costeMochilaParcela según el método.
     */
    public function costeOperacionParcela(Fumigacion $fum): float
    {
        return $fum->metodo_aplicacion === 'tractor'
            ? $this->costeTractorParcela($fum)
            : $this->costeMochilaParcela($fum);
    }

    /**
     * Desglose por producto (dosis media introducida, cantidad total aplicada
     * y coste total) para un conjunto de fumigaciones ya de un único método,
     * repartido con la misma proporción que el coste (evita contar de más los
     * pases de tractor/mochila que cubren varias parcelas a la vez).
     */
    public function productosPorFumigaciones(Collection $fums): array
    {
        $acumulado = [];

        foreach ($fums as $fum) {
            $unidades = $fum->metodo_aplicacion === 'tractor' ? $fum->turbos : $fum->mochilas;
            $proporcion = $this->calcularProporcion($fum);

            foreach ($fum->productos as $prod) {
                $dosis = (float) $prod->pivot->dosis_introducida;
                $cantidad = $dosis * $unidades * $proporcion;
                $precioUnidad = (float) ($prod->pivot->precio ?? $prod->precio ?? 0);

                if (!isset($acumulado[$prod->id])) {
                    $acumulado[$prod->id] = [
                        'producto_id' => $prod->id,
                        'nombre' => $prod->nombre,
                        'unidad' => $prod->unidad,
                        'dosisSuma' => 0.0,
                        'dosisCuenta' => 0,
                        'cantidadTotal' => 0.0,
                        'costeTotal' => 0.0,
                    ];
                }

                $acumulado[$prod->id]['dosisSuma'] += $dosis;
                $acumulado[$prod->id]['dosisCuenta']++;
                $acumulado[$prod->id]['cantidadTotal'] += $cantidad;
                $acumulado[$prod->id]['costeTotal'] += $cantidad * $precioUnidad;
            }
        }

        return array_values(array_map(function ($p) {
            return [
                'producto_id' => $p['producto_id'],
                'nombre' => $p['nombre'],
                'unidad' => $p['unidad'],
                'dosisMedia' => round($p['dosisSuma'] / $p['dosisCuenta'], 2),
                'cantidadTotal' => round($p['cantidadTotal'], 2),
                'costeTotal' => round($p['costeTotal'], 2),
            ];
        }, $acumulado));
    }
}
