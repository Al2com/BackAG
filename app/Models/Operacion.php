<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class Operacion extends Model{
    use PerteneceAdmin;
    protected $table = 'operaciones';

        protected $fillable = [
        'parcela_id',
        'usuario_id',
        'operario',
        'tipo_operacion',
        'producto_id',
        'dosis',
        'hora_inicio',
        'duracion_minutos',
        'precio',
        'descripcion',
];

//una operación pertenece usuario,
public function usuario(){
    return $this->belongsTo(User::class , 'id_usuario');
}
//una operacion pertenece a una parcela

public function parcela(){
    return $this->belongsTo(Parcela::class);
}

//herencia Fumigaciones(es el padre)

public function fumigacion(){
    return $this->hasOne(Fumigacion::class);
}

// producto consumido cuando tipo_operacion = 'abonado'
public function producto(){
    return $this->belongsTo(Producto::class);
}

}
