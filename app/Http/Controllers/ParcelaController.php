<?php
namespace App\Http\Controllers;
use App\Models\Parcela;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    public function infoParcelas(){
        $parcelas = Parcela::all();

        $numParcelas = $parcelas->count();
        $TotalHng = 0;
        $parGot = 0;
        $parMan = 0;

        foreach($parcelas as $parcela){
            $TotalHng += $parcela->dimension_hanegadas;
            if($parcela->rol === 'goteo') $parGot++;
            if($parcela->rol === 'manta') $parMan++;
        }
        return response()->json([
            'total' => $numParcelas,
            'totalHng' => round($TotalHng, 2),
            'parcelasgoteo' => $parGot,
            'parcelasmanta' => $parMan
        ]);
    }

    public function resumenDetallado(){
        $resumDatParcelas = Parcela::with('explotacion:id,nombre')
            ->get([
                'id',
                'nombre',
                'poligono',
                'parcela',
                'dimension_hanegadas',
                'variedad',
                'num_arboles',
                'fecha_plantacion',
                'explotacion_id',
                'rol'
            ]);
        return response()->json($resumDatParcelas);
    }

    public function crearParcela(Request $request){
        $parcela = $request->validate([
            'explotacion_id'      => 'required',
            'propietarios_id'     => 'required',
            'nombre'              => 'required|max:25',
            'poligono'            => 'required|max:25',
            'parcela'             => 'required',
            'rol'                 => 'required',
            'variedad'            => 'required',
            'num_arboles'         => 'required',
            'dimension_hanegadas' => 'required',
            'fecha_plantacion'    => 'required',
            'descripcion'         => 'required',
            'impuesto_municipal'  => 'nullable|numeric|min:0',
            'impuesto_cequiaje'   => 'nullable|numeric|min:0',
        ]);
        Parcela::create($parcela);
        return response()->json(['mensaje' => 'Parcela creada'], 201);
    }

    public function listarParcelas(){
        $parcelas = Parcela::select('id', 'nombre', 'poligono', 'parcela', 'variedad')->get();
        return response()->json($parcelas);
    }

    public function show($id){
        $parcela = Parcela::findOrFail($id);
        return response()->json($parcela);
    }

    public function actualizar(Request $request, $id){
        $parcela = Parcela::findOrFail($id);

        $validacion = $request->validate([
            'explotacion_id'      => 'required',
            'propietarios_id'     => 'required',
            'nombre'              => 'required|max:25',
            'poligono'            => 'required',
            'parcela'             => 'required',
            'rol'                 => 'required|in:goteo,manta',
            'variedad'            => 'required',
            'dimension_hanegadas' => 'required',
            'num_arboles'         => 'required',
            'fecha_plantacion'    => 'required',
            'fecha_plantacion'    => 'required',
            'descripcion'         => 'required',
            'impuesto_municipal'  => 'nullable|numeric|min:0',
            'impuesto_cequiaje'   => 'nullable|numeric|min:0',
        ]);

        $parcela->update($validacion);
        return response()->json(['mensaje' => 'Parcela actualizada correctamente'], 200);
    }

    public function borrar($id) {
        $parcela = Parcela::findOrFail($id);
        $parcela->delete();
        return response()->json(['mensaje' => 'Parcela eliminada correctamente']);
    }
}