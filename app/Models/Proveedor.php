<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class Proveedor extends Model
{
    use PerteneceAdmin;

    protected $table = 'proveedores';
    protected $fillable = [
        'nombre_empresa',
        'direccion',
        'telefono',
        'nombre_comercial',
        'descripcion',
        // 'admin_id'
    ];

    public function compraProducto(){
        return $this->hasMany(CompraProducto::class);
    }
}