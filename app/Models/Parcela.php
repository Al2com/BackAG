<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Explotacion;
use App\Models\Propietario;
use App\Models\Fumigacion;
use App\Models\Scopes\AdminScope;


class Parcela extends Model
{
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
    'admin_id'
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

protected static function booted(): void{
    static::addGlobalScope(new AdminScope());
}


}
