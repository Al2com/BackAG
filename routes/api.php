<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExplotacionController;
use App\Http\Controllers\ParcelaController;

Route::get('/explotaciones', [ExplotacionController::class, 'numeroExplo']);
Route::get('/parcelas', [ParcelaController::class, 'infoParcelas']);
//Para el apartado de explotaciones
Route::get('/explotaciones/resumen' , [ExplotacionController::class,'resumenExplotaciones']);
//para parcelas
Route::get('/parcelas/resumen' , [ParcelaController::class,'resumenParcelas']);







