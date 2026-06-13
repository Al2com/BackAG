<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExplotacionController;
use App\Http\Controllers\ParcelaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\OperacionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\FumigacionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TareasController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CompraProductoController;
use App\Http\Controllers\ProveedorController;




Route::middleware('auth:sanctum')->group(function () {
    // EXPLOTACIONES
    Route::get('/explotaciones', [ExplotacionController::class, 'numeroExplo']);
    Route::get('/explotaciones/resumen', [ExplotacionController::class, 'resumenExplotaciones']);
    Route::post('/explotaciones/crear', [ExplotacionController::class, 'crear']);
    Route::get('/explotaciones/{id}', [ExplotacionController::class, 'show']);
    Route::put('/explotaciones/{id}', [ExplotacionController::class, 'actualizar']);
    Route::delete('/explotaciones/{id}', [ExplotacionController::class, 'borrarExplo']);

    // PARCELAS
    Route::get('/parcelas', [ParcelaController::class, 'infoParcelas']);
    Route::get('/parcelas/resumen', [ParcelaController::class, 'resumenDetallado']);
    Route::get('/parcelas/lista', [ParcelaController::class, 'listarParcelas']);
    Route::post('/parcelas/crear', [ParcelaController::class, 'crearParcela']);
    Route::get('/parcelas/{id}', [ParcelaController::class, 'show']);
    Route::put('/parcelas/{id}', [ParcelaController::class, 'actualizar']);
    Route::delete('/parcelas/{id}', [ParcelaController::class, 'borrar']);

    // USUARIOS Y PROPIETARIOS & trabajadores
    Route::get('/usuarios', [UserController::class, 'mostrarUsers']);
    Route::get('/trabajadores', [UserController::class, 'mostrarTrabajadores']);
    Route::post('/trabajadores', [UserController::class, 'crearTrabajador']);
    Route::get('/propietarios', [PropietarioController::class, 'mostrarPropietarios']);

    // OPERACIONES
    Route::get('/operaciones', [OperacionController::class, 'listar']);
    Route::post('/operaciones/crear', [OperacionController::class, 'crearOperacion']);
    Route::get('/operaciones/{id}', [OperacionController::class, 'opercionId']);
    Route::delete('/operaciones/{id}', [OperacionController::class, 'borrar']);
    Route::put('/operaciones/{id}', [OperacionController::class, 'actualizar']);

    // PRODUCTOS
    Route::get('/productos/lista', [ProductoController::class, 'mostrarProductos']);
    Route::get('/productos/lista/{id}', [ProductoController::class, 'modificarProducto']);
    Route::put('/productos/lista/{id}', [ProductoController::class, 'actualizarProducto']);

    // FUMIGACIONES
    Route::get('/fumigaciones', [FumigacionController::class, 'listar']);
    Route::post('/fumigaciones/crear', [FumigacionController::class, 'añadirFumigacion']);
    Route::delete('/fumigaciones/{id}', [FumigacionController::class, 'borrar']);
    Route::get('/fumigaciones/{id}', [FumigacionController::class, 'mostrar']);
    Route::put('/fumigaciones/{id}', [FumigacionController::class, 'actualizar']);

    // TAREAS
    Route::get('/tareas', [TareasController::class, 'listar']);
    Route::get('/tareas/actividad-reciente', [TareasController::class, 'actividadReciente']);
    Route::put('/tareas/{tipo}/{id}', [TareasController::class, 'marcarRealizada']);
    Route::put('/tareas/{tipo}/{id}/revisada', [TareasController::class, 'marcarRevisada']);

    // ALMACEN
    Route::get('/almacen/stock-bajo', [AlmacenController::class, 'stockBajo']);
    Route::post('/almacen/crear', [AlmacenController::class, 'crear']);

    // COMPRAS
    Route::get('/compras', [CompraProductoController::class, 'listar']);
    Route::post('/compras/crear', [CompraProductoController::class, 'crear']);

    // PROVEEDORES
    Route::get('/proveedores', [ProveedorController::class, 'listar']);

    // AUTH
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registro', [AuthController::class, 'registro']);




// bórrala después de usarla
// Route::get('/reset-pass', function () {
//     \App\Models\User::where('email', 'alvaro@alvaro.com')
//         ->update(['password' => bcrypt('nueva_contraseña')]);
//     return 'Contraseña actualizada';
// });
//Para establecer un rol nuevo
// Route::get('/crear-usuario', function () {
//     \App\Models\User::create([
//         'name' => 'Nombre Encargado',
//         'email' => 'encargado@test.com',
//         'password' => bcrypt('contraseña'),
//         'rol' => 'admin', // o el rol que uses
//     ]);
//     return 'Usuario creado';
// });