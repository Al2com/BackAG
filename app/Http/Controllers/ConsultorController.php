<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultorRequest;
use App\Services\ConsultorContextoService;
use App\Services\ConsultorPromptService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class ConsultorController extends Controller
{
    public function __construct(
        private ConsultorContextoService $contexto,
        private ConsultorPromptService $prompt
    ) {}

    public function consultar(ConsultorRequest $request)
    {
        $pregunta = $request->input('pregunta');

        // Determina la campaña: si la pregunta menciona un año concreto o "campaña anterior",
        // ajustamos el rango; si no, usamos la campaña actual.
        [$inicio, $fin] = $this->resolverRangoCampana($pregunta);

        $etiquetaCampana = $this->contexto->etiquetaCampana($inicio, $fin)
            . " ({$inicio->format('d/m/Y')} - {$fin->format('d/m/Y')})";

        $contextoTexto = $this->contexto->construir($pregunta, $inicio, $fin);

        $mensajes = $this->prompt->construir(
            fechaHoy: Carbon::today()->format('d/m/Y'),
            campanaActual: $etiquetaCampana,
            contextoPorModulos: $contextoTexto,
            pregunta: $pregunta
        );

        try {
            $respuesta = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post(config('services.groq.url'), [
                    'model'       => config('services.groq.modelo'),
                    'temperature' => 0.1,
                    'max_tokens'  => 1000,
                    'messages'    => [
                        ['role' => 'system', 'content' => $mensajes['sistema']],
                        ['role' => 'user',   'content' => $mensajes['usuario']],
                    ],
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException) {
            return response()->json([
                'error'   => true,
                'message' => 'El servicio de consultor no respondió a tiempo. Inténtalo de nuevo.',
            ], 504);
        }

        if ($respuesta->status() === 429) {
            return response()->json([
                'error'   => true,
                'message' => 'Has hecho varias consultas seguidas. Espera unos segundos antes de volver a preguntar.',
            ], 429);
        }

        if (!$respuesta->successful()) {
            return response()->json([
                'error'   => true,
                'message' => 'El servicio de consultor no está disponible ahora. Inténtalo más tarde.',
            ], 502);
        }

        $datos = $respuesta->json();
        $texto = $datos['choices'][0]['message']['content'] ?? null;

        if (!$texto) {
            return response()->json([
                'error'   => true,
                'message' => 'El consultor devolvió una respuesta vacía. Inténtalo de nuevo.',
            ], 502);
        }

        return response()->json([
            'respuesta' => $this->sanearRespuesta($texto),
        ]);
    }

    // Extrae un año de la pregunta (p. ej. "campaña 2024", "en 2023") y ajusta el rango.
    // Si no hay año explícito, usa la campaña actual.
    private function resolverRangoCampana(string $pregunta): array
    {
        $campanaActual = $this->contexto->campanaActual();
        $inicio = $campanaActual['inicio'];
        $fin    = $campanaActual['fin'];

        // Busca patrones como "2024/2025", "campaña 2024", "en 2023", "año 2022"
        if (preg_match('/\b(20\d{2})\s*[\/\-]\s*(20\d{2})\b/', $pregunta, $m)) {
            $anioIni = (int) $m[1];
            $inicio = Carbon::create($anioIni, ConsultorContextoService::INICIO_CAMPANIA_MES, ConsultorContextoService::INICIO_CAMPANIA_DIA)->startOfDay();
            $fin    = Carbon::create($anioIni + 1, ConsultorContextoService::FIN_CAMPANIA_MES, ConsultorContextoService::FIN_CAMPANIA_DIA)->endOfDay();
        } elseif (preg_match('/\b(20\d{2})\b/', $pregunta, $m)) {
            $anio = (int) $m[1];
            // Si el año está dentro de una campaña, tomamos esa campaña completa
            if ($anio >= 10) {
                // si el año mencionado podría ser inicio de campaña
                $inicioCandidata = Carbon::create($anio, ConsultorContextoService::INICIO_CAMPANIA_MES, ConsultorContextoService::INICIO_CAMPANIA_DIA)->startOfDay();
                $finCandidata    = Carbon::create($anio + 1, ConsultorContextoService::FIN_CAMPANIA_MES, ConsultorContextoService::FIN_CAMPANIA_DIA)->endOfDay();
                $inicio = $inicioCandidata;
                $fin    = $finCandidata;
            }
        }

        return [$inicio, $fin];
    }

    // Elimina marcas de Markdown que saldrían como caracteres sueltos en texto plano.
    // No toca guiones bajos ni guiones normales para no romper nombres de productos.
    private function sanearRespuesta(string $texto): string
    {
        // Asteriscos en cualquier posición
        $texto = str_replace('*', '', $texto);
        // Comillas invertidas en cualquier posición
        $texto = str_replace('`', '', $texto);
        // Almohadillas solo al inicio de línea (no rompe "Parcela #3")
        $texto = preg_replace('/^#+\s*/m', '', $texto);
        // Más de dos saltos de línea consecutivos → dos
        $texto = preg_replace('/\n{3,}/', "\n\n", $texto);

        return trim($texto);
    }
}
