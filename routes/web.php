<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Las vistas Blade antiguas (user, explotaciones, parcelas, insertarExplo,
// editar, actualizar) se eliminaron: eran públicas, sin autenticación, y
// todos los datos se sirven ya por la API protegida con auth:sanctum.
//
// Tampoco quedan las pantallas de Breeze (login, register, forgot-password,
// dashboard, profile): el único acceso es la API que consume el front React
// (/api/login, /api/forgot-password, /api/reset-password).
