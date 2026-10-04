<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CrearUsuario extends Command
{
    protected $signature = 'usuario:crear {email} {nombre} {--rol=admin : admin o superadmin}';

    protected $description = 'Crea una cuenta nueva (el registro público está cerrado). Pide la contraseña sin mostrarla.';

    public function handle(): int
    {
        // la contraseña se pide por teclado para que no quede en el historial
        $password = $this->secret('Contraseña (mínimo 8 caracteres, con letras y números)');

        $datos = [
            'name' => $this->argument('nombre'),
            'email' => $this->argument('email'),
            'password' => $password,
            'rol' => $this->option('rol'),
        ];

        $validador = Validator::make($datos, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'rol' => ['required', 'in:admin,superadmin'],
        ]);

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $usuario = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'rol' => $datos['rol'],
        ]);

        $this->info("Usuario creado: {$usuario->email} (rol {$usuario->rol}, id {$usuario->id})");

        return self::SUCCESS;
    }
}
