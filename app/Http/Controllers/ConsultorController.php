<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultorRequest;
use App\Services\ConsultorContextoService;
use App\Services\ConsultorPromptService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $apiKey = config('services.groq.api_key');
        $modelo = config('services.groq.modelo');
        $url    = config('services.groq.url');

        // Si la clave no está configurada, falla rápido con un mensaje claro
        if (empty($apiKey)) {
            Log::error('ConsultorController: GROQ_API_KEY no está configurada en .env');
            return response()->json([
                'error'   => true,
                'message' => 'El consultor no está configurado. Contacta con el administrador.',
            ], 500);
        }

        try {
            $respuesta = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($url, [
                    'model'       => $modelo,
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
            // Loguea el error real de Groq para poder diagnosticarlo
            Log::error('ConsultorController: error de Groq', [
                'status' => $respuesta->status(),
                'body'   => $respuesta->body(),
                'modelo' => $modelo,
                'url'    => $url,
            ]);
            return response()->json([
                'error'   => true,
                'message' => 'El servicio de consultor no está disponible ahora. Inténtalo más tarde.',
            ], 502);
        }

        $datos = $respuesta->json();
        $texto = $datos['choices'][0]['message']['content'] ?? null;

        if (!$texto) {
            Log::warning('ConsultorController: respuesta vacía de Groq', ['body' => $respuesta->body()]);
            return response()->json([
                'error'   => true,
                'message' => 'El consultor devolvió una respuesta vacía. Inténtalo de nuevo.',
            ], 502);
        }

        return response()->json([
            'respuesta' => $this->sanearRespuesta($texto),
        ]);
    }

    private function resolverRangoCampana(string $pregunta): array
    {
        $campanaActual = $this->contexto->campanaActual();
        $inicio = $campanaActual['inicio'];
        $fin    = $campanaActual['fin'];

        if (preg_match('/\b(20\d{2})\s*[\/\-]\s*(20\d{2})\b/', $pregunta, $m)) {
            $anioIni = (int) $m[1];
            $inicio = Carbon::create($anioIni, ConsultorContextoService::INICIO_CAMPANIA_MES, ConsultorContextoService::INICIO_CAMPANIA_DIA)->startOfDay();
            $fin    = Carbon::create($anioIni + 1, ConsultorContextoService::FIN_CAMPANIA_MES, ConsultorContextoService::FIN_CAMPANIA_DIA)->endOfDay();
        } elseif (preg_match('/\b(20\d{2})\b/', $pregunta, $m)) {
            $anio   = (int) $m[1];
            $inicio = Carbon::create($anio, ConsultorContextoService::INICIO_CAMPANIA_MES, ConsultorContextoService::INICIO_CAMPANIA_DIA)->startOfDay();
            $fin    = Carbon::create($anio + 1, ConsultorContextoService::FIN_CAMPANIA_MES, ConsultorContextoService::FIN_CAMPANIA_DIA)->endOfDay();
        }

        return [$inicio, $fin];
    }

    private function sanearRespuesta(string $texto): string
    {
        $texto = str_replace('*', '', $texto);
        $texto = str_replace('`', '', $texto);
        $texto = preg_replace('/^#+\s*/m', '', $texto);
        $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
        return trim($texto);
    }
}
