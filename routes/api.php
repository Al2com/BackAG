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

//EXPLOTACIONES
Route::get('/explotaciones', [ExplotacionController::class, 'numeroExplo']);
Route::get('/explotaciones/resumen', [ExplotacionController::class, 'resumenExplotaciones']);
Route::post('/explotaciones/crear', [ExplotacionController::class, 'crear']);
Route::get('/explotaciones/{id}', [ExplotacionController::class, 'show']);
Route::put('/explotaciones/{id}', [ExplotacionController::class, 'actualizar']);

//PARCELAS
// IMPORTANTE: las rutas estáticas SIEMPRE antes que las dinámicas {id}
Route::get('/parcelas', [ParcelaController::class, 'infoParcelas']);
Route::get('/parcelas/resumen', [ParcelaController::class, 'resumenDetallado']);
Route::get('/parcelas/lista', [ParcelaController::class, 'listarParcelas']);
Route::post('/parcelas/crear', [ParcelaController::class, 'crearParcela']);
Route::get('/parcelas/{id}', [ParcelaController::class, 'show']);
Route::put('/parcelas/{id}', [ParcelaController::class, 'actualizar']);

//USUARIOS Y PROPIETARIOS
Route::get('/usuarios', [UserController::class, 'mostrarUsers']);
Route::get('/propietarios', [PropietarioController::class, 'mostrarPropietarios']);

//OPERACIONE
Route::get('/operaciones', [OperacionController::class, 'listar']);
Route::post('/operaciones/crear', [OperacionController::class, 'crearOperacion']);

//PRODUCTOS
Route::get('/productos/lista', [ProductoController::class, 'mostrarProductos']);

//FUMIGACIONES
Route::get('/fumigaciones', [FumigacionController::class, 'listar']);
Route::post('/fumigaciones/crear', [FumigacionController::class, 'añadirFumigacion']);

//AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//TAREAS
Route::get('/tareas', [TareasController::class, 'listar']);
Route::put('/tareas/{tipo}/{id}', [TareasController::class, 'marcarRealizada']);
Route::put('/tareas/{tipo}/{id}/revisada', [TareasController::class, 'marcarRevisada']);

//ALMACEN
Route::post('/almacen/crear', [AlmacenController::class, 'crear']);
