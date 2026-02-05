<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; //fijarse en la rota tal cual esta
use App\Http\Controllers\ExplotacionController; //fijarse en la rota tal cual esta
use App\Http\Controllers\ParcelaController; //importar el controlador

Route::get('/', function () {
    return view('welcome');
});
//vista usuarios
Route::get('user',[UserController::class,'mostrar']);
//vista explotaciones
// Route::get('explotaciones',[ExplotacionController::class,'mostrarExplotaciones']);
//vista parcelas
Route::get('parcelas',[ParcelaController::class,'mostrarParcelas']);
//vista expotaciones blade
Route::get('/explotaciones', [ExplotacionController::class,'mostrarExplotaciones']);





Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
