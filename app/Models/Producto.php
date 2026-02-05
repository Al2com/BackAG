<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    $protected $fillable =[
        'cantidad',
        'dosis_introducida'
    ];

    //relacion

    public function Fumigacion(){
        return $this->belongsToMany(Fumigacion::class, 'fumigacion_producto');
          ->withPivot('cantidad', 'dosis_introducida')//referencia a la tabla intermedia

    }

    public function Proveedor(){
        return $this->belongsToMany(Proveedor::class , 'compra_productos' )
    }

    public function users(){
        return $this->belongsToMany(User::class,'compra_producto','producto_id','user_id')
        ->withPivot('cantidad', 'precio', 'fecha_compra');
    }

}
