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




Route::get('/explotaciones', [ExplotacionController::class, 'numeroExplo']);
//Para el apartado de explotaciones
Route::get('/explotaciones/resumen' , [ExplotacionController::class,'resumenExplotaciones']);
//para crear las explotaciones
Route::post('/explotaciones/crear' , [ExplotacionController::class,'crear']);

Route::get('/parcelas', [ParcelaController::class, 'infoParcelas']);

//para parcelas
Route::get('/parcelas/resumen' , [ParcelaController::class,'resumenDetallado']);

//para mostrar los usuarios para el formExplotaciones
Route::get('/usuarios' , [UserController::class,'mostrarUsers']);

//para mostrar los propietarios para el formExplotaciones
Route::get('propietarios', [PropietarioController::class,'mostrarPropietarios']);

//para crear parcelas
Route::post('/parcelas/crear',[ParcelaController::class,'crearParcela']);

//para ver las parcelas en operaciones
Route::get('/parcelas/lista', [ParcelaController::class, 'listarParcelas']);
//para devolver los datos de la parcela
Route::get('/parcelas/{id}' , [ParcelaController::class, 'datosParcela']);
//para actualizar los datos de la parcela
Route::put('/parcelas/{id}', [ParcelaController::class, 'actualizarParcela']);

Route::get('/operaciones', [OperacionController::class, 'listar']);

//para crear operaciones
Route::post('/operaciones/crear', [OperacionController::class, 'crearOperacion']);

//para mostrar los productos quimicos
Route::get('/productos/lista' , [ProductoController::class, 'mostrarProductos']);


//para crear fumigacion
Route::post('/fumigaciones/crear',[FumigacionController::class,'añadirFumigacion']);

//para traer la fumigaciones al menu de operaciones
Route::get('/fumigaciones', [FumigacionController::class, 'listar']);

//para los roles y el token
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


//ruta para ver las tareas(todas)
Route::get('/tareas', [TareasController::class, 'listar']);
//ruta put para cambiar el estado de pendiente a realizada
Route::put('/tareas/{tipo}/{id}',[TareasController::class,'marcarRealizada']);
//ruta put para cambiar el estado de realizada a revisada
Route::put('/tareas/{tipo}/{id}/revisada', [TareasController::class, 'marcarRevisada']);

//ruta para añadir productos nuevos al almacen
Route::post('/almacen/crear', [AlmacenController::class, 'crear']);
