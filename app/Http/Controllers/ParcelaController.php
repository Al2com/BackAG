<?php
namespace App\Http\Controllers;
use App\Models\Parcela;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    // cuenta las parcelas y suma hanegadas, tipo riego para los paneles de info
    public function infoParcelas(){
        $numParcelas = Parcela::count();
        $TotalHng = 0;
        $parGot = 0;
        $parMan = 0;
        $parcelas = Parcela::all();
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

    // trae todas las parcelas con el nombre de su explotacion para mostrarlas en la tabla
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

    // crea una parcela nueva con los datos que manda el formulario
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
        ]);
        Parcela::create($parcela);
        return response()->json(['mensaje' => 'Parcela creada'], 201);
    }

    // trae solo id, nombre, poligono y variedad para los selects de operaciones
    public function listarParcelas(){
        $parcelas = Parcela::select('id', 'nombre', 'poligono', 'parcela', 'variedad')->get();
         return response()->json($parcelas);
    }

    // busca la parcela por id y manda sus datos al formulario de edicion
    public function show($id){
        $parcela = Parcela::findOrFail($id);
        return response()->json($parcela);
    }

    // recibe los datos del formulario de edicion y los guarda en la base de datos
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
            'descripcion'         => 'required',
        ]);

        $parcela->update($validacion);
        return response()->json(['mensaje' => 'Parcela actualizada correctamente'], 200);
    }
}
