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

//una operación pertenece usuario,
public function usuario(){
    return $this->belongsTo(User::class 'id_usuario');
}
//una operacion pertenece a una parcela

public function parcela(){
    return $this->belongsTo(Parcela::class);
}
}
