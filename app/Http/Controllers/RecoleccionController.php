<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recoleccion;
use App\Models\Parcela;

class RecoleccionController extends Controller
{
    // GET /api/recolecciones
    public function listar()
    {
        $recolecciones = Recoleccion::with('parcela:id,nombre,poligono,parcela,variedad')
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($recolecciones);
    }

    // POST /api/recolecciones/crear
    public function crear(Request $request)
    {
        $datos = $request->validate([
            'parcela_id'      => 'required|exists:parcelas,id',
            'fecha'           => 'required|date',
            'tipo'            => 'required|in:adelanto,normal,atraso',
            'kilos'           => 'required|numeric|min:0',
            'precio_medio_kg' => 'required|numeric|min:0',
        ]);

        // copia de la fruta del momento, leída de la parcela (ya filtrada por admin)
        $parcela = Parcela::findOrFail($datos['parcela_id']);
        $datos['variedad'] = $parcela->variedad;

        $recoleccion = Recoleccion::create($datos); // admin_id lo pone el trait

        return response()->json($recoleccion, 201);
    }

    // GET /api/recolecciones/{id}
    public function mostrar($id)
    {
        $recoleccion = Recoleccion::findOrFail($id);
        return response()->json($recoleccion);
    }

    // PUT /api/recolecciones/{id}
    public function actualizar(Request $request, $id)
    {
        $recoleccion = Recoleccion::findOrFail($id);

        $datos = $request->validate([
            'fecha'           => 'required|date',
            'tipo'            => 'required|in:adelanto,normal,atraso',
            'kilos'           => 'required|numeric|min:0',
            'precio_medio_kg' => 'required|numeric|min:0',
        ]);

        $recoleccion->update($datos);

        return response()->json($recoleccion);
    }

    // DELETE /api/recolecciones/{id}
    public function borrar($id)
    {
        $recoleccion = Recoleccion::findOrFail($id);
        $recoleccion->delete();
        return response()->json(['mensaje' => 'Recolección eliminada']);
    }
}