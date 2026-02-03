<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operacion extends Model
{
    protected $table = 'operaciones';

     protected $fillable = [
        'parcela_id',
        'usuario_id',
        'tipo_operacion',
        'hora_inicio',
        'duracion_minutos',
        'descripcion'
    ];

//fata hacer las relaciones 
}
