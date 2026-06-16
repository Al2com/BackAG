<?php

namespace App\Services;

use App\Models\Fumigacion;

class CosteFumigacionService
{
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
}