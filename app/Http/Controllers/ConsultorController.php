<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Parcela;
use App\Models\Operacion;
use App\Models\Fumigacion;

class ConsultorController extends Controller
{
    public function consultar(Request $request) {
        $mensajes = $request->input('mensajes');

        $anioActual = now()->year;

        $parcelas = Parcela::all(['id', 'nombre', 'poligono', 'parcela', 'variedad', 'dimension_hanegadas']);

        $operaciones = Operacion::with('parcela')
            ->whereYear('hora_inicio', $anioActual)
            ->get(['id', 'parcela_id', 'tipo_operacion', 'operario', 'precio', 'hora_inicio', 'estado']);

        $fumigaciones = Fumigacion::with('parcela')
            ->whereYear('hora_inicio', $anioActual)
            ->get(['id', 'parcela_id', 'metodo_aplicacion', 'operario', 'hora_inicio', 'estado']);

        $contexto = "Año actual: {$anioActual}\n\n";

        $contexto .= "PARCELAS DE LA EXPLOTACIÓN:\n";
        foreach ($parcelas as $p) {
            $nombre = $p->nombre ?: "Pol.{$p->poligono}-Par.{$p->parcela}";
            $contexto .= "- {$nombre} (ID:{$p->id}, {$p->dimension_hanegadas} hanegadas)\n";
        }

        $contexto .= "\nOPERACIONES {$anioActual}:\n";
        foreach ($operaciones as $o) {
            $nombreParcela = $o->parcela?->nombre ?: "Parcela {$o->parcela_id}";
            $contexto .= "- {$nombreParcela}: {$o->tipo_operacion}, operario: {$o->operario}, coste: {$o->precio}€, estado: {$o->estado}\n";
        }

        $contexto .= "\nFUMIGACIONES {$anioActual}:\n";
        foreach ($fumigaciones as $f) {
            $nombreParcela = $f->parcela?->nombre ?: "Parcela {$f->parcela_id}";
            $contexto .= "- {$nombreParcela}: {$f->metodo_aplicacion}, operario: {$f->operario}, estado: {$f->estado}\n";
        }

        $respuesta = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'openai/gpt-oss-20b',
            'max_tokens' => 2000,
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

        return $respuesta->json();
    }
}