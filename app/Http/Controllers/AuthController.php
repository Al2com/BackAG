<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; //añadir para token
use Illuminate\Support\Facades\Auth; //añadir para token
use Illuminate\Support\Facades\Hash;  //añadir para token

class AuthController extends Controller
{
    //login => recibe el mail y el password, verifica con Auth::attempt(), si es correcto genra token
   public function login(Request $request){
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        return response()->json([
            'token' => Auth::user()->createToken('token')->plainTextToken,
            'usuario' => Auth::user(),
            'rol' => Auth::user()->rol
        ]);
    }

    return response()->json(['mensaje' => 'Credenciales incorrectas'], 401);
}


    public function registro(Request $request){
    // registro público cerrado salvo que se active en config (REGISTRO_ABIERTO)
    if (! config('app.registro_abierto')) {
        return response()->json(['mensaje' => 'El registro de nuevas cuentas está cerrado'], 403);
    }

    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users'],
        'password' => ['required', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
    ]);

    $usuario = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'rol' => 'admin',
    ]);

    return response()->json([
        'token' => $usuario->createToken('token')->plainTextToken,
        'usuario' => $usuario,
        'rol' => $usuario->rol
    ]);
}

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada correctamente']);
    }



}