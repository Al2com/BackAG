<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AdminScope;

class Producto extends Model
{

    protected $fillable = [
    'nombre',
    'materia_activa',
    'precio',
    'ubicacion',
    'stock_minimo',
    'stock_actual',
    'unidad',
    'dosis_recomendada',
    'admin_id'
];

    //relacion

    public function Fumigacion(){
        return $this->belongsToMany(Fumigacion::class, 'fumigacion_producto')
          ->withPivot('cantidad', 'dosis_introducida');//referencia a la tabla intermedia

    }

    protected static function booted(): void{
        static::addGlobalScope(new AdminScope());
    }
//se quitan por añadir CompraProducto porque va a gestionar las 3 entidades
    // public function Proveedor(){
    //     return $this->belongsToMany(Proveedor::class , 'compra_productos' );
    // }

    // public function users(){
    //     return $this->belongsToMany(User::class,'compra_producto','producto_id','user_id')
    //     ->withPivot('cantidad', 'precio', 'fecha_compra');
    // }

}
