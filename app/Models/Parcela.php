<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Explotacion;
use App\Models\Propietario;
use App\Models\Fumigacion;
use App\Models\Concerns\PerteneceAdmin;



class Parcela extends Model{
    use PerteneceAdmin;
    protected $table = 'parcelas';

protected $fillable = [
    'explotacion_id',
    'propietarios_id',
    'nombre',
    'rol',
    'poligono',
    'parcela',
    'variedad',
    'dimension_hanegadas',
    'num_arboles',
    'fecha_plantacion',
    'descripcion',
    'admin_id',
    'impuesto_municipal',
    'impuesto_cequiaje',
];

    //relaciones

    public function explotacion(){
        return $this->belongsTo(Explotacion::class);

    }

    public function propietario(){
        return $this->belongsTo(Propietario::class);
    }

   //Una parcela tiene muchas operaciones

   public function operaciones(){
        return $this->hasMany(Operacion::class ,'parcela_id');//clave foranea id_parcela(esta en operaciones)
   }

//con esto puedo ver es un historial de fumigaciones de cada parcela
   public function fumigaciones(){
    return $this->hasMany(Fumigacion::class, 'parcela_id');
}

//relación con entidad gastos_riego
public function gastosRiego(){
        return $this->hasMany(GastoRiego::class);
    }
//relacion con la recoleccion
    public function recolecciones(){
        return $this->hasMany(Recoleccion::class, 'parcela_id');
    }

}
