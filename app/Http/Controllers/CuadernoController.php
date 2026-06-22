<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fumigacion;

class CuadernoController extends Controller{
    // Datos para rellenar el cuaderno de campo de la cooperativa.
    // Las 7 columnas del impreso son las FECHAS de tratamiento de la campaña
    // (no las parcelas): se trata a todas las parcelas el mismo día. Por eso
    // devolvemos las fechas distintas (máx 7) y, por cada producto usado, en qué
    // columnas lleva la X. El front lo cruza con el catálogo impreso.
    //
    // GET /api/cuaderno-campo?anio=2026&cultivo=citrico   (cultivo: 'citrico' | 'kaki')
    public function generar(Request $request)
    {
        $anio    = (int) $request->query('anio', date('Y'));
        $cultivo = $request->query('cultivo', 'citrico');

        // El AdminScope ya limita las fumigaciones al admin logueado.
        $fumigaciones = Fumigacion::with(['parcela', 'Productos'])
            ->whereYear('hora_inicio', $anio)
            ->get()
            // clasifico por cultivo según la variedad de la parcela:
            // "rojo brillante" = kaki; el resto = cítrico (regla invertida para
            // que una variedad nueva de cítrico no se quede fuera).
            ->filter(function ($f) use ($cultivo) {
                $v = strtolower(trim(optional($f->parcela)->variedad ?? ''));
                $tipo = str_contains($v, 'rojo brillante') ? 'kaki' : 'citrico';
                return $tipo === $cultivo;
            });

        // Fechas distintas = columnas (ordenadas, máximo 7)
        $fechas = $fumigaciones
            ->map(fn ($f) => substr($f->hora_inicio, 0, 10)) // Y-m-d
            ->unique()
            ->sort()
            ->values()
            ->take(7);

        // fecha (Y-m-d) -> nº de columna 1..7
        $colDeFecha = [];
        foreach ($fechas as $i => $fecha) {
            $colDeFecha[$fecha] = $i + 1;
        }

        // Por cada producto usado: nombre, materia activa y columnas con X
        $productos = [];
        foreach ($fumigaciones as $f) {
            $fecha = substr($f->hora_inicio, 0, 10);
            if (!isset($colDeFecha[$fecha])) {
                continue; // tratamiento fuera de las 7 columnas
            }
            $col = $colDeFecha[$fecha];

            foreach ($f->Productos as $p) {
                if (!isset($productos[$p->id])) {
                    $productos[$p->id] = [
                        'nombre'         => $p->nombre,
                        'materia_activa' => $p->materia_activa,
                        'columnas'       => [],
                    ];
                }
                if (!in_array($col, $productos[$p->id]['columnas'])) {
                    $productos[$p->id]['columnas'][] = $col;
                }
            }
        }

        return response()->json([
            'anio'     => $anio,
            'cultivo'  => $cultivo,
            'parcelas' => 'Todas las parcelas',
            'fechas'   => $fechas->map(fn ($d) => date('j/n', strtotime($d)))->values(),
            'productos' => array_values($productos),
        ]);
    }
}