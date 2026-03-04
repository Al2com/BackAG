<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fumigacion extends Model
{

     protected $table = 'fumigaciones';
    protected $fillable = [
    'parcela_id',
    'usuario_id',
    'operario',
    'metodo_aplicacion',
    'hora_inicio',
    'duracion_minutos',
    'mochilas',
    'turbos',
    'descripcion'
];

    //Fumigacion es una herencia de Operaciones
    public function Operaciones(){
        return $this->belongsTo(Operacion::class);
    }

    //relacion con productos(aunque es la parte del 1 en Laravel se ecribe asi)la tabla intermedia no tiene modelo
//no se si tengo que hacer referencia a ella

    public function Productos(){
        return $this->belongsToMany(Producto::class, 'fumigacion_producto')
        ->withPivot('cantidad','dosis_introducida');
    }
}

