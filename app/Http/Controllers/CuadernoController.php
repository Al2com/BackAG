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
    // Hay dos bloques con la misma estructura: fitosanitarios y "manejo del
    // suelo" (herbicidas). Se reparten según la materia activa del producto.
    //
    // GET /api/cuaderno-campo?anio=2026&cultivo=citrico   (cultivo: 'citrico' | 'kaki')

    // Materias activas que van al bloque "Manejo del suelo" (herbicidas).
    // Se clasifica por materia activa, NO por método (mochila/tractor): son
    // cosas independientes y así no se cuela un fitosanitario aplicado a mochila.
    private const MATERIAS_HERBICIDA = [
        'glifosato 36%',
        'mcpa 50%',
        'oxifluorfén 24%',
    ];

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

        // Reparto cada aplicación (producto + fecha) en dos cubos: herbicidas
        // (manejo del suelo) y el resto (fitosanitarios).
        $appFito  = [];
        $appSuelo = [];
        foreach ($fumigaciones as $f) {
            $fecha = substr($f->hora_inicio, 0, 10); // Y-m-d
            foreach ($f->Productos as $p) {
                $registro = ['fecha' => $fecha, 'producto' => $p];
                if ($this->esHerbicida($p)) {
                    $appSuelo[] = $registro;
                } else {
                    $appFito[] = $registro;
                }
            }
        }

        $fito = $this->construirBloque($appFito);

        return response()->json([
            'anio'        => $anio,
            'cultivo'     => $cultivo,
            'parcelas'    => 'Todas las parcelas',
            'fechas'      => $fito['fechas'],      // fitosanitarios (igual que antes)
            'productos'   => $fito['productos'],
            'manejoSuelo' => $this->construirBloque($appSuelo),
        ]);
    }

    // ¿la materia activa del producto está en la lista de herbicidas?
    private function esHerbicida($producto): bool
    {
        $materia = mb_strtolower(trim($producto->materia_activa ?? ''));
        return in_array($materia, self::MATERIAS_HERBICIDA, true);
    }

    // De una lista de aplicaciones [fecha, producto] arma un bloque del cuaderno:
    // las fechas distintas (columnas, máx 7) y, por producto, en qué columnas va
    // la X. Mismo criterio para fitosanitarios y para herbicidas.
    private function construirBloque(array $aplicaciones): array
    {
        // Fechas distintas = columnas (ordenadas, máximo 7)
        $fechas = collect($aplicaciones)
            ->pluck('fecha')
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
        foreach ($aplicaciones as $a) {
            if (!isset($colDeFecha[$a['fecha']])) {
                continue; // tratamiento fuera de las 7 columnas
            }
            $col = $colDeFecha[$a['fecha']];
            $p   = $a['producto'];

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

        return [
            'fechas'    => $fechas->map(fn ($d) => date('j/n', strtotime($d)))->values(),
            'productos' => array_values($productos),
        ];
    }
}