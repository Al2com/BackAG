<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $fillable = [
        'nombre_empresa',
        'direccion',
        'telefono',
        'nombre_comercial',
        'descripcion',
    ];

    public function compraProducto(){
        return $this->hasMany(CompraProducto::class);
    }
}