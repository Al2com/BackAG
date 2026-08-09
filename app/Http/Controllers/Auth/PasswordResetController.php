<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // Paso 1: el usuario pide el enlace
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        // Mensaje genérico siempre, para no revelar qué emails están registrados
        if (in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER])) {
            return response()->json([
                'message' => 'Si la cuenta existe, recibirás un correo con el enlace de recuperación.'
            ]);
        }

        // Solo llega aquí si hay throttling u otro error real
        return response()->json([
            'message' => 'No se pudo procesar la solicitud. Inténtalo de nuevo en unos minutos.'
        ], 429);
    }

    // Paso 2: el usuario envía la nueva contraseña con el token
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Contraseña actualizada correctamente.'])
            : response()->json(['message' => 'El enlace no es válido o ha caducado.'], 422);
    }
}