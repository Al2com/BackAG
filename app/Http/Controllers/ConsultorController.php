<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Parcela;
use App\Models\Operacion;
use App\Models\Fumigacion;
use App\Models\Recoleccion;
use App\Services\CosteFumigacionService;
use App\Services\RiegoService;

class ConsultorController extends Controller
{
    public function __construct(
        private CosteFumigacionService $coste,
        private RiegoService $riego
    ) {}

    public function consultar(Request $request)
    {
        $mensajes = $request->input('mensajes');

        $anioActual = now()->year;

        $parcelas = Parcela::all(['id', 'nombre', 'poligono', 'parcela', 'variedad', 'dimension_hanegadas', 'impuesto_municipal', 'impuesto_cequiaje']);
        $parcelaIds = $parcelas->pluck('id');

        $operaciones = Operacion::whereYear('hora_inicio', $anioActual)
            ->get(['id', 'parcela_id', 'tipo_operacion', 'precio']);
        // total por parcela y tipo (poda, abonado, mantenimiento, tractor): agrupado aquí
        // en vez de mandar cada operación individual, para no disparar el tamaño del prompt
        $operacionesPorParcelaYTipo = $operaciones
            ->groupBy('parcela_id')
            ->map(fn($ops) => $ops->groupBy('tipo_operacion')
                ->map(fn($grupo) => round((float) $grupo->sum('precio'), 2)));

        $fumigaciones = Fumigacion::with(['parcela', 'productos'])
            ->whereYear('hora_inicio', $anioActual)
            ->get();
        $fumigaciones = $this->coste->enriquecerHanegadas($fumigaciones);
        $costeFumigacionPorParcela = [];
        foreach ($fumigaciones as $f) {
            $costeFumigacionPorParcela[$f->parcela_id] = ($costeFumigacionPorParcela[$f->parcela_id] ?? 0)
                + $this->coste->costeOperacionParcela($f)
                + $this->coste->costeMaterialParcela($f);
        }

        $costeRiegoPorParcela = $this->riego->costeTotalPorParcela(
            $parcelaIds,
            [$anioActual],
            "{$anioActual}-01-01",
            "{$anioActual}-12-31"
        );

        $ingresosPorParcela = Recoleccion::whereIn('parcela_id', $parcelaIds)
            ->whereYear('fecha', $anioActual)
            ->selectRaw('parcela_id, SUM(kilos) as kilos, SUM(kilos * precio_medio_kg) as ingreso')
            ->groupBy('parcela_id')
            ->get()
            ->keyBy('parcela_id');

        // un total agregado por parcela (no fila a fila) para no disparar los tokens
        // del prompt: el modelo recibe cifras ya sumadas, no tiene que calcularlas él
        $contexto = "Año actual: {$anioActual}\n\n";
        $contexto .= "DATOS POR PARCELA {$anioActual} (gastos y recolección):\n";

        foreach ($parcelas as $p) {
            $nombre = $p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}";

            $gastosTipo = $operacionesPorParcelaYTipo->get($p->id, collect());
            $gastoOperaciones = round((float) $gastosTipo->sum(), 2);
            $detalleTipos = $gastosTipo->map(fn($importe, $tipo) => "{$tipo} {$importe}€")->implode(', ');

            $gastoFumigacion = round((float) ($costeFumigacionPorParcela[$p->id] ?? 0), 2);
            $gastoRiego = round((float) ($costeRiegoPorParcela->get($p->id) ?? 0), 2);
            $impuestos = round((float) ($p->impuesto_municipal ?? 0) + (float) ($p->impuesto_cequiaje ?? 0), 2);
            $gastoTotal = round($gastoOperaciones + $gastoFumigacion + $gastoRiego + $impuestos, 2);

            $filaIngreso = $ingresosPorParcela->get($p->id);
            $recoleccion = $filaIngreso
                ? "{$filaIngreso->kilos} kg, ingreso " . round((float) $filaIngreso->ingreso, 2) . '€'
                : 'sin recolección registrada';

            $contexto .= "- {$nombre} (ID:{$p->id}, variedad: {$p->variedad}, {$p->dimension_hanegadas} hanegadas): "
                . ($detalleTipos ? "{$detalleTipos}, " : '')
                . "fumigación {$gastoFumigacion}€, riego {$gastoRiego}€, impuestos {$impuestos}€, "
                . "gasto total {$gastoTotal}€ | recolección: {$recoleccion}\n";
        }

        try {
            $respuesta = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'openai/gpt-oss-20b',
                    'max_tokens' => 800,
                    'messages' => array_merge([
                        [
                            'role' => 'system',
                            'content' => "Eres un asistente agrícola especializado en la explotación de caqui y cítricos en Valencia.
Responde siempre en español, de forma clara y concisa.
Cuando no encuentres un dato, dilo claramente.
Usa los siguientes datos reales de la explotación para responder:

{$contexto}"
                        ]
                    ], $mensajes),
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => true,
                'message' => 'El servicio de IA no respondió a tiempo. Inténtalo de nuevo.',
            ], 504);
        }

        if ($respuesta->status() === 429) {
            return response()->json([
                'error' => true,
                'message' => 'Has hecho varias preguntas seguidas. Espera unos segundos antes de volver a preguntar.',
            ], 429);
        }

        if (!$respuesta->successful()) {
            return response()->json([
                'error' => true,
                'message' => 'Error al consultar el servicio de IA. Inténtalo de nuevo.',
            ], 502);
        }

        return $respuesta->json();
    }
}
