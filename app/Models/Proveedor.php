<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    $protected $fillable = [

        'nombre',
        'materia_activa',
        'precio',
        'ubicacion',
        'stock_minimo',
    ];

    public function Producto(){
        return $this->belongsToMany(Producto::class ,'compra_productos')
    }
}
