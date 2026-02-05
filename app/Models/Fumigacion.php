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

    //Fumigacion es una herencia de Operaciones
    public function Operaciones(){
        return $this->belongTo(Operacion::class);
    }

    //relacion con productos(aunque es la parte del 1 en Laravel se ecribe asi)la tabla intermedia no tiene modelo
//no se si tengo que hacer referencia a ella

    public function Productos(){
        return $this->belongsToMany(Producto::class, 'fumigacion_producto');
        ->withPivot('cantidad','dosis_introducida');
    }
}

