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




//EXPLOTACIONES
Route::get('/explotaciones', [ExplotacionController::class, 'numeroExplo']);
Route::get('/explotaciones/resumen', [ExplotacionController::class, 'resumenExplotaciones']);
Route::post('/explotaciones/crear', [ExplotacionController::class, 'crear']);
Route::get('/explotaciones/{id}', [ExplotacionController::class, 'show']);
Route::put('/explotaciones/{id}', [ExplotacionController::class, 'actualizar']);
Route::delete('/explotaciones/{id}', [ExplotacionController::class, 'borrarExplo']);


//PARCELAS
// IMPORTANTE: las rutas estaticas SIEMPRE antes que las dinamicas {id}
Route::get('/parcelas', [ParcelaController::class, 'infoParcelas']);
Route::get('/parcelas/resumen', [ParcelaController::class, 'resumenDetallado']);
Route::get('/parcelas/lista', [ParcelaController::class, 'listarParcelas']);
Route::post('/parcelas/crear', [ParcelaController::class, 'crearParcela']);
Route::get('/parcelas/{id}', [ParcelaController::class, 'show']);
Route::put('/parcelas/{id}', [ParcelaController::class, 'actualizar']);
Route::delete('/parcelas/{id}', [ParcelaController::class, 'borrar']);

//USUARIOS Y PROPIETARIOS
Route::get('/usuarios', [UserController::class, 'mostrarUsers']);
Route::get('/propietarios', [PropietarioController::class, 'mostrarPropietarios']);

//OPERACIONES
Route::get('/operaciones', [OperacionController::class, 'listar']);
// Route::post('/operaciones/crear', [OperacionController::class, 'crearOperacion']);
Route::get('/operaciones/{id}', [OperacionController::class, 'opercionId']);//estatica antes de la dinamica
Route::delete('/operaciones/{id}', [OperacionController::class, 'borrar']);
//PRODUCTOS
Route::get('/productos/lista', [ProductoController::class, 'mostrarProductos']);
Route::get('/productos/lista/{id}', [ProductoController::class, 'modificarProducto']);
Route::put('/productos/lista/{id}', [ProductoController::class, 'actualizarProducto']);


//FUMIGACIONES
Route::get('/fumigaciones', [FumigacionController::class, 'listar']);
// Route::post('/fumigaciones/crear', [FumigacionController::class, 'añadirFumigacion']);
Route::delete('/fumigaciones/{id}', [FumigacionController::class, 'borrar']);
//AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registro', [AuthController::class, 'registro']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//TAREAS
Route::get('/tareas', [TareasController::class, 'listar']);
Route::put('/tareas/{tipo}/{id}', [TareasController::class, 'marcarRealizada']);
Route::put('/tareas/{tipo}/{id}/revisada', [TareasController::class, 'marcarRevisada']);
Route::get('/tareas/actividad-reciente', [TareasController::class, 'actividadReciente']);

//ALMACEN
Route::post('/almacen/crear', [AlmacenController::class, 'crear']);

//COMPRAS PRODUCTO
Route::get('/compras', [CompraProductoController::class, 'listar']);


//PROVEEDORES
Route::get('/proveedores', [ProveedorController::class, 'listar']);

//Si no esta dentro de middlewere no se sabe quien la crea
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/operaciones/crear', [OperacionController::class, 'crearOperacion']);
    Route::post('/fumigaciones/crear', [FumigacionController::class, 'añadirFumigacion']);
    Route::post('/compras/crear', [CompraProductoController::class, 'crear']);
});
