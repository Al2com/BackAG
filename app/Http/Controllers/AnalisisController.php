<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\Operacion;
use App\Models\Fumigacion;
use App\Services\CosteFumigacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AnalisisController extends Controller
{
    public function __construct(private CosteFumigacionService $coste) {}

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
