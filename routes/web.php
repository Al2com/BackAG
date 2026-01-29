<?php

use App\Http\Controllers\ProfileControllers;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; //fijarse en la rota tal cual esta
use App\Http\Controllers\ExplotacionController; //fijarse en la rota tal cual esta

Route::get('/', function () {
    return view('welcome');
});
//vista usuarios
Route::get('user',[UserController::class,'mostrar']);
//vista explotaciones
Route::get('explotaciones',[ExplotacionController::class,'mostrarExplotaciones']);


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
