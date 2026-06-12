<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AdminScope;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $fillable = [
        'nombre_empresa',
        'direccion',
        'telefono',
        'nombre_comercial',
        'descripcion',
        'admin_id'
    ];

    public function compraProducto(){
        return $this->hasMany(CompraProducto::class);
    }

    protected static function booted(): void{
        static::addGlobalScope(new AdminScope());
    }
}