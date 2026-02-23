<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExplotacionController;
use App\Http\Controllers\ParcelaController;

Route::get('/explotaciones', [ExplotacionController::class, 'numeroExplo']);
//Para el apartado de explotaciones
Route::get('/explotaciones/resumen' , [ExplotacionController::class,'resumenExplotaciones']);
//para crear las explotaciones
Route::post('/explotaciones/crear' , [ExplotacionController::class,'crear']);

Route::get('/parcelas', [ParcelaController::class, 'infoParcelas']);

//para parcelas
Route::get('/parcelas/resumen' , [ParcelaController::class,'resumenDetallado']);

//para mostrar los usuarios para el formExplotaciones
Routes::get('/usuarios' , [UserController::class,'mostrarUsers']);








