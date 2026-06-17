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
use App\Http\Controllers\GastoRiegoController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\RecoleccionController;


Route::middleware('auth:sanctum')->group(function () {

    // ===== ABIERTAS A TODOS (incluido trabajador): dashboard + operaciones =====
    Route::get('/explotaciones', [ExplotacionController::class, 'numeroExplo']);   // contador dashboard
    Route::get('/parcelas', [ParcelaController::class, 'infoParcelas']);           // contador dashboard
    Route::get('/operaciones', [OperacionController::class, 'listar']);            // dashboard
    Route::get('/fumigaciones', [FumigacionController::class, 'listar']);          // dashboard
    Route::get('/productos/lista', [ProductoController::class, 'mostrarProductos']); // dashboard
    Route::get('/tareas', [TareasController::class, 'listar']);                    // pantalla operaciones
    Route::get('/tareas/actividad-reciente', [TareasController::class, 'actividadReciente']); // dashboard
    Route::put('/tareas/{tipo}/{id}', [TareasController::class, 'marcarRealizada']); // el trabajador marca hecho
    Route::get('/almacen/stock-bajo', [AlmacenController::class, 'stockBajo']);     // dashboard
    Route::post('/logout', [AuthController::class, 'logout']);

    // ===== SOLO ADMINISTRADOR =====
    Route::middleware('admin')->group(function () {

        // EXPLOTACIONES
        Route::get('/explotaciones/resumen', [ExplotacionController::class, 'resumenExplotaciones']);
        Route::post('/explotaciones/crear', [ExplotacionController::class, 'crear']);
        Route::get('/explotaciones/{id}', [ExplotacionController::class, 'show']);
        Route::put('/explotaciones/{id}', [ExplotacionController::class, 'actualizar']);
        Route::delete('/explotaciones/{id}', [ExplotacionController::class, 'borrarExplo']);

        // PARCELAS
        Route::get('/parcelas/resumen', [ParcelaController::class, 'resumenDetallado']);
        Route::get('/parcelas/lista', [ParcelaController::class, 'listarParcelas']);
        Route::post('/parcelas/crear', [ParcelaController::class, 'crearParcela']);
        Route::get('/parcelas/{id}', [ParcelaController::class, 'show']);
        Route::put('/parcelas/{id}', [ParcelaController::class, 'actualizar']);
        Route::delete('/parcelas/{id}', [ParcelaController::class, 'borrar']);

        // USUARIOS / PROPIETARIOS / TRABAJADORES
        Route::get('/usuarios', [UserController::class, 'mostrarUsers']);
        Route::get('/trabajadores', [UserController::class, 'mostrarTrabajadores']);
        Route::post('/trabajadores', [UserController::class, 'crearTrabajador']);
        Route::get('/propietarios', [PropietarioController::class, 'mostrarPropietarios']);

        // OPERACIONES (crear / ver / editar / borrar)
        Route::post('/operaciones/crear', [OperacionController::class, 'crearOperacion']);
        Route::get('/operaciones/{id}', [OperacionController::class, 'opercionId']);
        Route::delete('/operaciones/{id}', [OperacionController::class, 'borrar']);
        Route::put('/operaciones/{id}', [OperacionController::class, 'actualizar']);

        // PRODUCTOS (editar)
        Route::get('/productos/lista/{id}', [ProductoController::class, 'modificarProducto']);
        Route::put('/productos/lista/{id}', [ProductoController::class, 'actualizarProducto']);

        // FUMIGACIONES
        Route::post('/fumigaciones/crear', [FumigacionController::class, 'añadirFumigacion']);
        Route::delete('/fumigaciones/{id}', [FumigacionController::class, 'borrar']);
        Route::get('/fumigaciones/{id}', [FumigacionController::class, 'mostrar']);
        Route::put('/fumigaciones/{id}', [FumigacionController::class, 'actualizar']);

        // TAREAS (revisar: solo admin)
        Route::put('/tareas/{tipo}/{id}/revisada', [TareasController::class, 'marcarRevisada']);

        // ALMACEN
        Route::post('/almacen/crear', [AlmacenController::class, 'crear']);

        // COMPRAS
        Route::get('/compras', [CompraProductoController::class, 'listar']);
        Route::post('/compras/crear', [CompraProductoController::class, 'crear']);

        // PROVEEDORES
        Route::get('/proveedores', [ProveedorController::class, 'listar']);

        // GASTOS DE RIEGO
        Route::get('/gastos-riego', [GastoRiegoController::class, 'listar']);
        Route::post('/gastos-riego', [GastoRiegoController::class, 'guardar']);
        Route::get('/gastos-riego/{parcelaId}/{anio}/{mes}', [GastoRiegoController::class, 'porMes']);
        Route::delete('/gastos-riego/{id}', [GastoRiegoController::class, 'borrar']);

        // RESUMEN DE GASTOS
        Route::get('/gastos/resumen', [GastosController::class, 'resumen']);

        // RECOLECCIONES
        Route::get('/recolecciones', [RecoleccionController::class, 'listar']);
        Route::post('/recolecciones/crear', [RecoleccionController::class, 'crear']);
        Route::get('/recolecciones/{id}', [RecoleccionController::class, 'mostrar']);
        Route::put('/recolecciones/{id}', [RecoleccionController::class, 'actualizar']);
        Route::delete('/recolecciones/{id}', [RecoleccionController::class, 'borrar']);
        //PROVEEDORES       
        Route::post('/proveedores', [ProveedorController::class, 'crear']);
    });

});

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registro', [AuthController::class, 'registro']);