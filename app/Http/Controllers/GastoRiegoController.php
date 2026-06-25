<?php

namespace App\Http\Controllers;


use App\Models\GastoRiego;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GastoRiegoController extends Controller
{
    // Guarda el recibo de un mes: una fila por cada concepto con importe
    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'parcela_id'    => ['required', Rule::exists('parcelas', 'id')->where('admin_id', $request->user()->adminId())],
            'anio'          => 'required|integer|min:2000|max:2100',
            'mes'           => 'required|integer|min:1|max:12',
            'agua'          => 'nullable|numeric|min:0',
            'abono'         => 'nullable|numeric|min:0',
            'mantenimiento' => 'nullable|numeric|min:0',
        ]);

        foreach (['agua', 'abono', 'mantenimiento'] as $concepto) {
            $importe = $datos[$concepto] ?? null;

            // si no viene importe para ese concepto, se ignora
            if ($importe === null || $importe === '') {
                continue;
            }

            GastoRiego::updateOrCreate(
                [
                    'parcela_id' => $datos['parcela_id'],
                    'anio'       => $datos['anio'],
                    'mes'        => $datos['mes'],
                    'concepto'   => $concepto,
                ],
                ['importe' => $importe]
            );
        }

        return response()->json(['mensaje' => 'Gasto de riego guardado'], 201);
    }

    // Devuelve lo ya registrado de una parcela en un mes/año (para precargar el formulario)
    public function porMes($parcelaId, $anio, $mes)
    {
        $gastos = GastoRiego::where('parcela_id', $parcelaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->get(['concepto', 'importe']);

        return response()->json($gastos);
    }
    // lista todos los recibos de riego del admin (el scope ya filtra)
    public function listar(){
        $gastos = GastoRiego::with('parcela:id,nombre,poligono,parcela,rol')
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->get();

        return response()->json($gastos);
    }

    // borra un apunte de riego por id
    public function borrar($id){
        $gasto = GastoRiego::findOrFail($id);
        $gasto->delete();
        return response()->json(['mensaje' => 'Gasto de riego eliminado']);
    }
}