<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    protected $table = 'parcelas';

    protected $fillable = ['explotacion_id','propietarios_id' , 'rol', 'pol_parcela','variedad',];


    //relaciones

    public function explotaciones(){
        return $this->belongsTo(Explotacion::class);

    }

    public function propietario(){
        return $this->belongsTo(Propietario::class);
    }

}
