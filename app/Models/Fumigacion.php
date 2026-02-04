<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fumigacion extends Model
{

     protected $table = 'fumigacion';
     protected $fillable = [
        'parcela_id',
        'usuario_id',
        'tipo_operacion',
        'hora_inicio',
        'duracion_minutos',
        'descripcion'
    ];

    //Un producto tiene muchas fumigaciones
}
