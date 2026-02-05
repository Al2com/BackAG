<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Explotacion extends Model
{

protected $table = 'explotaciones'; //confunde la tabla para que no pase
     protected $fillable = [
        'nombre',
        'ubicacion',
        'descripcion',
        'user_id',
        'propietario_id'

    ];

    public function user(){
         return $this->belongsTo(User::class);
}

    public function propietario(){
          return $this->belongsTo(Propietario::class);
}

    public function parcelas(){
        return $this->hasMany(Parcela::class);
    }

}


