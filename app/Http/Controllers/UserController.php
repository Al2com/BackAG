<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function mostrarUsers(){
        $usuarios = User::where('rol', 'admin')->get();
        return response()->json(['usuarios' => $usuarios]);
    }

    // Lista los trabajadores (operarios) de la cuenta del usuario logueado
    public function mostrarTrabajadores(){
        $user = auth()->user();
        $adminId = $user->rol === 'trabajador' ? $user->admin_id : $user->id;

        $usuarios = User::where('rol', 'trabajador')
            ->where('admin_id', $adminId)
            ->get(['id', 'name', 'email']);

        return response()->json(['usuarios' => $usuarios]);
    }

    // Crea un trabajador colgado del admin logueado
    public function crearTrabajador(Request $request){
        $user = auth()->user();

        // solo un admin (o superadmin) puede dar de alta operarios
        if (! in_array($user->rol, ['admin', 'superadmin'])) {
            return response()->json(['mensaje' => 'No autorizado para crear operarios'], 403);
        }

        $datos = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        $trabajador = User::create([
            'name'     => $datos['name'],
            'email'    => $datos['email'],
            'password' => Hash::make($datos['password']),
            'rol'      => 'trabajador',
            'admin_id' => $user->id,
        ]);

        return response()->json([
            'mensaje' => 'Operario creado correctamente',
            'usuario' => [
                'id'    => $trabajador->id,
                'name'  => $trabajador->name,
                'email' => $trabajador->email,
            ],
        ], 201);
    }
}