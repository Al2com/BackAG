<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\RiegoManta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RiegoMantaController extends Controller
{
    // lista riegos a manta (el scope global ya filtra por admin), opcionalmente
    // por parcela y/o año, para el listado y para precargar el calendario
    public function listar(Request $request)
    {
        $query = RiegoManta::with('parcela:id,nombre,poligono,parcela,dimension_hanegadas')
            ->orderByDesc('fecha');

        if ($request->query('parcela_id')) {
            $query->where('parcela_id', $request->query('parcela_id'));
        }
        if ($request->query('anio')) {
            $query->whereYear('fecha', $request->query('anio'));
        }

        return response()->json($query->get());
    }

    // riegos de un mes concreto, para pintar el calendario
    public function calendario(Request $request)
    {
        $anio = $request->query('anio', now()->year);
        $mes = $request->query('mes', now()->month);

        $riegos = RiegoManta::with('parcela:id,nombre,poligono,parcela')
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->get();

        return response()->json($riegos);
    }

    // crea un riego a manta para una o varias parcelas a la vez (mismo día,
    // mismo precio/hanegada). Si son varias, comparten lote_id; el importe de
    // cada una se calcula con SUS PROPIAS hanegadas en este momento
    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'fecha' => 'required|date',
            'precio_por_hanegada' => 'required|numeric|min:0',
            'parcela_ids' => 'required|array|min:1',
            'parcela_ids.*' => [
                'integer',
                Rule::exists('parcelas', 'id')
                    ->where('admin_id', $request->user()->adminId())
                    ->where('rol', 'manta'),
            ],
        ]);

        $parcelas = Parcela::whereIn('id', $datos['parcela_ids'])->get()->keyBy('id');
        $esGrupo = count($datos['parcela_ids']) > 1;
        $loteId = $esGrupo ? (string) Str::uuid() : null;

        $creados = collect($datos['parcela_ids'])->map(function ($parcelaId) use ($datos, $parcelas, $loteId) {
            $hanegadas = (float) $parcelas[$parcelaId]->dimension_hanegadas;

            return RiegoManta::create([
                'parcela_id' => $parcelaId,
                'lote_id' => $loteId,
                'fecha' => $datos['fecha'],
                'precio_por_hanegada' => $datos['precio_por_hanegada'],
                'hanegadas' => $hanegadas,
                'importe' => round($hanegadas * $datos['precio_por_hanegada'], 2),
            ]);
        });

        return response()->json(['mensaje' => 'Riego a manta guardado', 'riegos' => $creados], 201);
    }

    // edita un riego individual: recalcula el importe con las hanegadas YA
    // guardadas (histórico), no con las actuales de la parcela
    public function actualizar(Request $request, $id)
    {
        $riego = RiegoManta::findOrFail($id);

        $datos = $request->validate([
            'fecha' => 'required|date',
            'precio_por_hanegada' => 'required|numeric|min:0',
        ]);

        $riego->update([
            'fecha' => $datos['fecha'],
            'precio_por_hanegada' => $datos['precio_por_hanegada'],
            'importe' => round($riego->hanegadas * $datos['precio_por_hanegada'], 2),
        ]);

        return response()->json($riego);
    }

    // edita todas las filas de un lote a la vez (mismo día/precio para el grupo)
    public function actualizarLote(Request $request, $loteId)
    {
        $riegos = RiegoManta::where('lote_id', $loteId)->get();
        if ($riegos->isEmpty()) {
            return response()->json(['mensaje' => 'Lote no encontrado'], 404);
        }

        $datos = $request->validate([
            'fecha' => 'required|date',
            'precio_por_hanegada' => 'required|numeric|min:0',
        ]);

        $riegos->each(fn($r) => $r->update([
            'fecha' => $datos['fecha'],
            'precio_por_hanegada' => $datos['precio_por_hanegada'],
            'importe' => round($r->hanegadas * $datos['precio_por_hanegada'], 2),
        ]));

        return response()->json(['mensaje' => 'Lote actualizado', 'riegos' => $riegos->fresh()]);
    }

    public function borrar($id)
    {
        $riego = RiegoManta::findOrFail($id);
        $riego->delete();
        return response()->json(['mensaje' => 'Riego a manta eliminado']);
    }

    public function borrarLote($loteId)
    {
        $borrados = RiegoManta::where('lote_id', $loteId)->delete();
        if ($borrados === 0) {
            return response()->json(['mensaje' => 'Lote no encontrado'], 404);
        }
        return response()->json(['mensaje' => 'Lote de riego eliminado']);
    }
}
