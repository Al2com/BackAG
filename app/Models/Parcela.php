<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Explotacion;
use App\Models\Propietario;


class Parcela extends Model
{
    protected $table = 'parcelas';

    protected $fillable = ['explotacion_id','propietario_id' , 'rol', 'pol_parcela','variedad',];


    //relaciones

    public function explotacion(){
        return $this->belongsTo(Explotacion::class);

    }

    public function propietario(){
        return $this->belongsTo(Propietario::class);
    }

   //Una parcela tiene muchas operaciones

   public function operaciones(){
        return $this->hasMany(Operacion::class 'parcela_id');//clave foranea id_parcela(esta en operaciones)
   }


}
