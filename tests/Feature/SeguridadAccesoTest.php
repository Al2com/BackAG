<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('las rutas web antiguas y las pantallas de Breeze ya no existen', function (string $ruta) {
    $this->get($ruta)->assertNotFound();
})->with([
    '/user',
    '/explotaciones',
    '/parcelas',
    '/insertarExplo',
    '/editar/1',
    '/register',
    '/login',
    '/forgot-password',
    '/dashboard',
    '/profile',
]);

test('no se puede registrar por las rutas web de Breeze', function () {
    $this->post('/register', [
        'name' => 'Intruso',
        'email' => 'intruso@example.com',
        'password' => 'intruso1234',
        'password_confirmation' => 'intruso1234',
    ])->assertNotFound();

    expect(User::where('email', 'intruso@example.com')->exists())->toBeFalse();
});

test('el registro público por la API está cerrado por defecto', function () {
    $this->postJson('/api/registro', [
        'name' => 'Intruso',
        'email' => 'intruso@example.com',
        'password' => 'intruso1234',
    ])->assertForbidden();

    expect(User::where('email', 'intruso@example.com')->exists())->toBeFalse();
});

test('el registro por la API funciona si se abre en la configuración', function () {
    config(['app.registro_abierto' => true]);

    $this->postJson('/api/registro', [
        'name' => 'Nuevo',
        'email' => 'nuevo@example.com',
        'password' => 'nuevo1234',
    ])->assertOk()->assertJsonPath('rol', 'admin');
});

test('la API sin token responde 401 aunque no se pida JSON', function () {
    $this->get('/api/tareas')->assertUnauthorized();
});

test('un trabajador no entra en las rutas de administrador', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $trabajador = User::factory()->create(['rol' => 'trabajador', 'admin_id' => $admin->id]);

    $this->actingAs($trabajador, 'sanctum')->getJson('/api/parcelas/lista')->assertForbidden();
    $this->actingAs($admin, 'sanctum')->getJson('/api/parcelas/lista')->assertOk();
});

test('el login por la API sigue funcionando', function () {
    User::factory()->create([
        'email' => 'alguien@example.com',
        'password' => Hash::make('clave1234'),
        'rol' => 'admin',
    ]);

    $this->postJson('/api/login', ['email' => 'alguien@example.com', 'password' => 'clave1234'])
        ->assertOk()
        ->assertJsonStructure(['token', 'usuario', 'rol']);

    $this->postJson('/api/login', ['email' => 'alguien@example.com', 'password' => 'incorrecta'])
        ->assertUnauthorized();
});

test('las respuestas llevan cabeceras de seguridad', function () {
    $this->get('/up')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});
